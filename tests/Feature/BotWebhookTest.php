<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessBotMessage;
use Tests\TestCase;

class BotWebhookTest extends TestCase
{
    public function test_webhook_verification_challenge_succeeds_with_valid_token(): void
    {
        config(['whatsapp.verify_token' => 'test_verify_token_123']);

        $response = $this->get('/api/webhook?hub_mode=subscribe&hub_verify_token=test_verify_token_123&hub_challenge=CHALLENGE_STRING');

        $response->assertStatus(200);
        $this->assertEquals('CHALLENGE_STRING', $response->getContent());
    }

    public function test_webhook_verification_fails_with_invalid_token(): void
    {
        config(['whatsapp.verify_token' => 'test_verify_token_123']);

        $response = $this->get('/api/webhook?hub_mode=subscribe&hub_verify_token=wrong_token&hub_challenge=CHALLENGE_STRING');

        $response->assertStatus(403);
    }

    public function test_webhook_post_rejects_request_without_signature(): void
    {
        config(['whatsapp.meta_app_secret' => 'test_secret_key']);

        $response = $this->postJson('/api/webhook', ['object' => 'whatsapp_business_account']);

        $response->assertStatus(403);
    }

    public function test_webhook_post_rejects_request_with_invalid_signature(): void
    {
        config(['whatsapp.meta_app_secret' => 'test_secret_key']);

        $response = $this->withHeaders([
            'X-Hub-Signature-256' => 'sha256=invalid_hash_string',
        ])->postJson('/api/webhook', ['object' => 'whatsapp_business_account']);

        $response->assertStatus(403);
    }

    public function test_webhook_post_accepts_valid_signature_and_dispatches_job(): void
    {
        Queue::fake();

        $secret = 'test_secret_key';
        config(['whatsapp.meta_app_secret' => $secret]);

        $payload = json_encode([
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'WABA_123',
                    'changes' => [
                        [
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'messages' => [
                                    [
                                        'from' => '919876543210',
                                        'id' => 'wamid.123',
                                        'type' => 'text',
                                        'text' => ['body' => 'hi'],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        $response = $this->call(
            'POST',
            '/api/webhook',
            [],
            [],
            [],
            [
                'HTTP_X_HUB_SIGNATURE_256' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $response->assertStatus(200);
        Queue::assertDispatched(ProcessBotMessage::class);
    }
}
