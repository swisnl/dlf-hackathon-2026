<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_homepage_redirects_guests_to_login(): void
    {
        $this->followingRedirects()->get('/')->assertOk()->assertSee('Inloggen');
    }
}
