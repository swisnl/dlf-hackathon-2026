<?php

namespace App\Session;

use Illuminate\Session\DatabaseSessionHandler as BaseDatabaseSessionHandler;
use Override;

/**
 * Stores sessions without the IP address and user agent Laravel adds by default (AVG: data minimisation).
 */
class DatabaseSessionHandler extends BaseDatabaseSessionHandler
{
    /**
     * @param array<string, mixed> $payload
     */
    #[Override]
    protected function addRequestInformation(&$payload): static
    {
        return $this;
    }
}
