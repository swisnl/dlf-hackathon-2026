#!/usr/bin/env node
/**
 * Claude bridge: lets the app (in the swis-dev container) use the Claude Code CLI on the Mac,
 * with the account that is logged in there, so no API key or LiteLLM is needed.
 *
 * Run it on the Mac, not in the container:  npm run claude-bridge
 *
 * It only listens on 127.0.0.1; Docker Desktop delivers traffic to host.docker.internal there.
 * Every request needs the shared token from CLAUDE_BRIDGE_TOKEN in .env. Claude runs without
 * tools, MCP servers, settings or session history, in an empty temporary directory.
 */
import { spawn } from 'node:child_process';
import { timingSafeEqual } from 'node:crypto';
import { existsSync, mkdtempSync, readFileSync, rmSync } from 'node:fs';
import { createServer } from 'node:http';
import { tmpdir } from 'node:os';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const HOST = '127.0.0.1';
const MAX_BODY_BYTES = 2 * 1024 * 1024;
const MAX_TIMEOUT_SECONDS = 600;
const MAX_CONCURRENT = 4;
// How long Claude Code gets to stop after SIGTERM before it is killed.
const KILL_GRACE_MS = 5000;

const config = { ...readDotEnv(join(dirname(fileURLToPath(import.meta.url)), '..', '.env')), ...process.env };
const token = config.CLAUDE_BRIDGE_TOKEN ?? '';
const port = Number(config.CLAUDE_BRIDGE_PORT || 8765);
const claudeBin = config.CLAUDE_BIN || 'claude';
let running = 0;

if (token.length < 16) {
    console.error('Zet CLAUDE_BRIDGE_TOKEN (minimaal 16 tekens) in .env, bijvoorbeeld met: openssl rand -hex 32');
    process.exit(1);
}

createServer(async (req, res) => {
    const started = Date.now();
    const send = (status, body) => {
        res.writeHead(status, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify(body));
        console.log(`${new Date().toISOString()} ${req.method} ${req.url} ${status} ${Date.now() - started}ms`);
    };

    if (req.method === 'GET' && req.url === '/health') {
        return send(200, { ok: true });
    }
    if (req.method !== 'POST' || req.url !== '/generate') {
        return send(404, { error: 'Not found.' });
    }
    if (!authorized(req.headers.authorization)) {
        return send(401, { error: 'Invalid token.' });
    }
    if (running >= MAX_CONCURRENT) {
        return send(429, { error: 'Too many concurrent requests.' });
    }

    let input;
    try {
        input = validate(JSON.parse(await readBody(req)));
    } catch (e) {
        return send(400, { error: e.message });
    }

    // Stop Claude Code when the client goes away (for example a PHP timeout), so it does not keep a slot.
    const abort = new AbortController();
    res.on('close', () => {
        if (!res.writableEnded) {
            abort.abort();
        }
    });

    running++;
    try {
        send(200, await runClaude(input, abort.signal));
    } catch (e) {
        send(502, { error: e.message });
    } finally {
        running--;
    }
}).listen(port, HOST, () => {
    console.log(`Claude bridge luistert op http://${HOST}:${port} (bereikbaar vanuit de container via host.docker.internal:${port})`);
});

function authorized(header) {
    const given = Buffer.from(String(header ?? '').replace(/^Bearer /, ''));
    const expected = Buffer.from(token);

    return given.length === expected.length && timingSafeEqual(given, expected);
}

function readBody(req) {
    return new Promise((resolve, reject) => {
        let size = 0;
        const chunks = [];
        req.on('data', (chunk) => {
            size += chunk.length;
            if (size > MAX_BODY_BYTES) {
                reject(new Error('Request body too large.'));
                req.destroy();
            } else {
                chunks.push(chunk);
            }
        });
        req.on('end', () => resolve(Buffer.concat(chunks).toString('utf8')));
        req.on('error', reject);
    });
}

function validate(body) {
    if (typeof body?.prompt !== 'string' || body.prompt.trim() === '') {
        throw new Error('A prompt is required.');
    }
    if (body.system != null && typeof body.system !== 'string') {
        throw new Error('The system prompt must be a string.');
    }
    // The model ends up as a CLI argument, so only allow model names and aliases.
    if (typeof body.model !== 'string' || !/^[A-Za-z0-9][A-Za-z0-9._\-[\]]*$/.test(body.model)) {
        throw new Error('Invalid model.');
    }
    if (body.schema != null && (typeof body.schema !== 'object' || Array.isArray(body.schema))) {
        throw new Error('The schema must be a JSON object.');
    }

    const timeout = Number(body.timeout ?? 120);

    return {
        prompt: body.prompt,
        system: body.system || null,
        model: body.model,
        schema: body.schema ?? null,
        timeout: Number.isFinite(timeout) ? Math.min(Math.max(timeout, 1), MAX_TIMEOUT_SECONDS) : 120,
    };
}

function runClaude({ prompt, system, model, schema, timeout }, signal) {
    const args = [
        '--print',
        '--output-format', 'json',
        '--model', model,
        '--no-session-persistence',
        '--tools', '',
        '--strict-mcp-config',
        '--setting-sources', '',
    ];
    if (system) {
        args.push('--system-prompt', system);
    }
    if (schema) {
        args.push('--json-schema', JSON.stringify(schema));
    }

    // An empty working directory, so no CLAUDE.md or project files end up in the context.
    const cwd = mkdtempSync(join(tmpdir(), 'claude-bridge-'));

    return new Promise((resolve, reject) => {
        // In its own process group, so stopping it also stops anything it started.
        const child = spawn(claudeBin, args, { cwd, stdio: ['pipe', 'pipe', 'pipe'], detached: true });
        const kill = (sig) => {
            try {
                process.kill(-child.pid, sig);
            } catch {
                // Already gone.
            }
        };
        let stdout = '';
        let stderr = '';
        let reason = null;
        let killTimer = null;
        const stop = (why) => {
            if (reason !== null || child.exitCode !== null) {
                return;
            }
            reason = why;
            kill('SIGTERM');
            killTimer = setTimeout(() => kill('SIGKILL'), KILL_GRACE_MS);
        };
        const timer = setTimeout(() => stop(`Claude Code stopped after ${timeout}s`), timeout * 1000);
        const onAbort = () => stop('The client closed the connection');
        signal?.addEventListener('abort', onAbort, { once: true });
        if (signal?.aborted) {
            onAbort();
        }

        child.stdout.on('data', (chunk) => (stdout += chunk));
        child.stderr.on('data', (chunk) => (stderr += chunk));
        child.on('error', (e) => finish(() => reject(new Error(`Could not start ${claudeBin}: ${e.message}`))));
        child.on('close', (code, exitSignal) => finish(() => {
            if (exitSignal) {
                return reject(new Error(`${reason ?? 'Claude Code stopped'} (${exitSignal}).`));
            }

            let result;
            try {
                result = JSON.parse(stdout);
            } catch {
                return reject(new Error(`Claude Code exited with ${code}: ${(stderr || stdout).trim().slice(-500)}`));
            }
            if (code !== 0 || result.is_error) {
                return reject(new Error(`Claude Code error: ${String(result.result ?? stderr).trim().slice(-500)}`));
            }

            resolve({
                text: result.result ?? '',
                structured: result.structured_output ?? null,
                model: Object.keys(result.modelUsage ?? {})[0] ?? model,
                stop_reason: result.stop_reason ?? null,
                usage: {
                    input_tokens: result.usage?.input_tokens ?? 0,
                    output_tokens: result.usage?.output_tokens ?? 0,
                    cache_read_input_tokens: result.usage?.cache_read_input_tokens ?? 0,
                    cache_creation_input_tokens: result.usage?.cache_creation_input_tokens ?? 0,
                },
            });
        }));

        child.stdin.on('error', () => {});
        child.stdin.end(prompt);

        let finished = false;
        function finish(callback) {
            if (finished) {
                return;
            }
            finished = true;
            clearTimeout(timer);
            clearTimeout(killTimer);
            signal?.removeEventListener('abort', onAbort);
            rmSync(cwd, { recursive: true, force: true });
            callback();
        }
    });
}

function readDotEnv(path) {
    if (!existsSync(path)) {
        return {};
    }

    const values = {};
    for (const line of readFileSync(path, 'utf8').split('\n')) {
        const match = line.match(/^\s*([A-Z0-9_]+)\s*=\s*(.*?)\s*$/);
        if (match) {
            values[match[1]] = match[2].replace(/^(['"])(.*)\1$/, '$2');
        }
    }

    return values;
}
