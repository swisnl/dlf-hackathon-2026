<?php

namespace App\Ai\ClaudeCode;

use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Contracts\Gateway\StepTextGateway;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\ProviderConnectionException;
use Laravel\Ai\Gateway\Concerns\DecodesStructuredOutput;
use Laravel\Ai\Gateway\StepContext;
use Laravel\Ai\Gateway\StepResponse;
use Laravel\Ai\Gateway\TextGenerationOptions;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\MessageRole;
use Laravel\Ai\ObjectSchema;
use Laravel\Ai\Responses\Data\FinishReason;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\TextUsage;

/**
 * Sends prompts to the Claude bridge (bin/claude-bridge.mjs), which runs the Claude Code CLI
 * on the developer's Mac. Claude runs there without tools, so agents with tools are not supported.
 */
class ClaudeCodeGateway implements StepTextGateway
{
    use DecodesStructuredOutput;

    public function generateTextStep(
        TextProvider $provider,
        string $model,
        ?string $instructions,
        array $messages,
        array $tools,
        ?array $schema,
        ?TextGenerationOptions $options,
        ?int $timeout,
        StepContext $stepContext,
    ): StepResponse {
        if ($tools !== []) {
            throw new AiException('The claude-code provider runs Claude Code without tools.');
        }

        /** @var ClaudeCodeProvider $provider */
        $timeout ??= 120;

        try {
            $response = Http::baseUrl($provider->url())
                ->withToken($provider->token())
                ->acceptJson()
                ->connectTimeout(5)
                // Give the bridge a little longer than Claude Code itself, so its own timeout error comes through.
                ->timeout($timeout + 10)
                ->post('generate', array_filter([
                    'model' => $model,
                    'system' => $instructions,
                    'prompt' => self::prompt($messages),
                    'schema' => filled($schema) ? (new ObjectSchema($schema))->toSchema() : null,
                    'timeout' => $timeout,
                ], fn (mixed $value): bool => $value !== null && $value !== ''));
        } catch (ConnectionException $e) {
            throw new ProviderConnectionException("The Claude bridge at {$provider->url()} is not reachable; start it on the Mac with `npm run claude-bridge`.", previous: $e);
        }

        $data = $response->fluent();

        if ($response->failed()) {
            throw new AiException('Claude bridge error: '.$data->string('error', $response->body()));
        }

        $text = $data->string('text')->toString();
        $structured = null;

        if (filled($schema)) {
            /** @var array<string, mixed> $structured */
            $structured = is_array($data->get('structured')) ? $data->get('structured') : $this->decodeStructuredOutput($text);
        }

        return new StepResponse(
            text: $text,
            toolCalls: [],
            finishReason: $data->get('stop_reason') === 'max_tokens' ? FinishReason::Length : FinishReason::Stop,
            usage: new TextUsage(
                inputTokens: $data->integer('usage.input_tokens'),
                outputTokens: $data->integer('usage.output_tokens'),
                cacheWriteInputTokens: $data->integer('usage.cache_creation_input_tokens'),
                cacheReadInputTokens: $data->integer('usage.cache_read_input_tokens'),
            ),
            meta: new Meta($provider->name(), $data->string('model', $model)->toString()),
            structured: $structured,
        );
    }

    public function generateStreamStep(
        string $invocationId,
        TextProvider $provider,
        string $model,
        ?string $instructions,
        array $messages,
        array $tools,
        ?array $schema,
        ?TextGenerationOptions $options,
        ?int $timeout,
        StepContext $stepContext,
    ): Generator {
        yield from [];

        throw new AiException('The claude-code provider does not support streaming.');
    }

    /**
     * Claude Code takes a single prompt, so a conversation is sent as a transcript ending with the newest message.
     *
     * @param array<Message> $messages
     */
    public static function prompt(array $messages): string
    {
        if (count($messages) === 1) {
            return (string) $messages[0]->content;
        }

        $transcript = array_map(
            fn (Message $message): string => sprintf(
                "<message role=\"%s\">\n%s\n</message>",
                $message->role === MessageRole::Assistant ? 'assistant' : 'user',
                $message->content,
            ),
            $messages,
        );

        return "This is the conversation so far. You are the assistant; reply to the last user message.\n\n"
            ."<conversation>\n".implode("\n", $transcript)."\n</conversation>";
    }
}
