# DLF hackathon 2026

## AI lokaal via Claude Code

De AI in de app (oefeningen maken, antwoorden beoordelen, doorpraten) draait via de Claude Code CLI op je Mac,
met het account waarmee je daar in `claude` bent ingelogd. Er is geen API-key nodig.

PHP draait in de swis-dev-container, waar geen `claude` is. Daarom draait er op de Mac een kleine bridge
(`bin/claude-bridge.mjs`) die de app aanroept via `http://host.docker.internal:8765`. De bridge:

- luistert alleen op `127.0.0.1` (Docker Desktop zet verkeer naar `host.docker.internal` daar neer);
- accepteert alleen verzoeken met het gedeelde token uit `CLAUDE_BRIDGE_TOKEN`;
- draait `claude --print --output-format json --no-session-persistence` zonder tools, MCP-servers of settings,
  in een lege tijdelijke map. Structured output (generator en beoordeling) gaat via `--json-schema`.

Eenmalig instellen in `.env`:

```dotenv
CLAUDE_BRIDGE_URL=http://host.docker.internal:8765
CLAUDE_BRIDGE_PORT=8765
CLAUDE_BRIDGE_TOKEN=   # bijvoorbeeld: openssl rand -hex 32
CLAUDE_CODE_MODEL=sonnet
```

Starten, **op de Mac** (niet via `swis-dev`), in de root van het project en laat het venster open:

```bash
npm run claude-bridge
```

Je moet in Claude Code zijn ingelogd (`claude` en dan `/login`). Draait de bridge niet, dan krijgt de student een
foutmelding en staat er in de log `The Claude bridge ... is not reachable`. Controleren kan met
`curl http://127.0.0.1:8765/health`.

De tests roepen nooit echte AI aan: de agents worden gefaket (`Agent::fake()`) en `Http::preventStrayRequests()` staat aan.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
