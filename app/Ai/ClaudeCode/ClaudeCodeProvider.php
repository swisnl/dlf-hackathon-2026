<?php

namespace App\Ai\ClaudeCode;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Laravel\Ai\Contracts\Gateway\StepTextGateway;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Providers\Concerns\GeneratesText;
use Laravel\Ai\Providers\Concerns\HasTextGateway;
use Laravel\Ai\Providers\Concerns\StreamsText;
use Laravel\Ai\Providers\Provider;
use Override;

/**
 * Text generation through the local Claude Code CLI, via the Claude bridge on the developer's Mac.
 */
class ClaudeCodeProvider extends Provider implements TextProvider
{
    use GeneratesText;
    use HasTextGateway;
    use StreamsText;

    /**
     * @param array<mixed> $config
     */
    public function __construct(protected array $config, protected Dispatcher $events)
    {
    }

    /**
     * @return array{key: mixed}
     */
    #[Override]
    public function providerCredentials(): array
    {
        return ['key' => $this->config['key'] ?? ''];
    }

    public function textGateway(): StepTextGateway
    {
        return $this->textGateway ??= new ClaudeCodeGateway();
    }

    public function url(): string
    {
        return rtrim($this->setting('url', 'http://host.docker.internal:8765'), '/');
    }

    public function token(): string
    {
        return $this->setting('key', '');
    }

    public function defaultTextModel(): string
    {
        return $this->setting('models.text.default', 'sonnet');
    }

    public function cheapestTextModel(): string
    {
        return $this->setting('models.text.cheapest', 'haiku');
    }

    public function smartestTextModel(): string
    {
        return $this->setting('models.text.smartest', 'opus');
    }

    private function setting(string $key, string $default): string
    {
        $value = Arr::get($this->config, $key);

        return is_string($value) && $value !== '' ? $value : $default;
    }
}
