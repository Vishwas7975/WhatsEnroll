<?php

namespace App\Services\WhatsApp;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected Client $client;
    protected string $phoneNumberId;
    protected string $apiToken;
    protected string $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->phoneNumberId = config('whatsapp.phone_number_id');
        $this->apiToken = config('whatsapp.api_token');
        $this->apiUrl = "https://graph.facebook.com/v18.0/{$this->phoneNumberId}/messages";
    }

    public function sendText(string $to, string $message): void
    {
        $this->send([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ]);
    }

    public function sendButtons(string $to, string $body, array $buttons): void
    {
        $this->send([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => $body],
                'action' => [
                    'buttons' => array_map(fn($btn) => [
                        'type' => 'reply',
                        'reply' => ['id' => $btn['id'], 'title' => $btn['title']],
                    ], $buttons),
                ],
            ],
        ]);
    }

    public function sendList(string $to, string $body, string $buttonText, array $sections): void
    {
        $this->send([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'body' => ['text' => $body],
                'action' => [
                    'button' => $buttonText,
                    'sections' => $sections,
                ],
            ],
        ]);
    }

    public function sendImage(string $to, string $imageUrl, string $caption = ''): void
    {
        $this->send([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'image',
            'image' => ['link' => $imageUrl, 'caption' => $caption],
        ]);
    }

    protected function send(array $payload): void
    {
        try {
            $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp API Error: ' . $e->getMessage());
        }
    }
}