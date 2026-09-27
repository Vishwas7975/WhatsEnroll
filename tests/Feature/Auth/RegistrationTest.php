<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_disabled(): void
    {
        // Public registration is intentionally disabled in WhatsEnroll (bot-first system)
        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_new_users_cannot_self_register(): void
    {
        // Registration endpoint must reject public submissions
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(404);
    }
}
