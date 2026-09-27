<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessBotMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    // Webhook verification (GET) — called by Meta to confirm your endpoint
    public function verify(Request $request)
    {
        $verifyToken = config('whatsapp.verify_token');

        if (
            $request->get('hub_mode') === 'subscribe' &&
            $request->get('hub_verify_token') === $verifyToken
        ) {
            return response($request->get('hub_challenge'), 200);
        }

        return response('Unauthorized', 403);
    }

    // Receive messages (POST) — validates Meta's signature, then queues processing
    public function handle(Request $request)
    {
        // ── 1. Verify X-Hub-Signature-256 (Meta App Secret HMAC) ──────────────
        // This prevents anyone from spoofing webhook payloads to your endpoint.
        // Meta signs every POST with HMAC-SHA256 of the raw body using your App Secret.
        $signature = $request->header('X-Hub-Signature-256');
        $appSecret = config('whatsapp.meta_app_secret');

        if (!$signature || !$appSecret) {
            Log::warning('WhatsApp webhook: missing signature or app secret not configured.');
            return response('Unauthorized', 403);
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('WhatsApp webhook: invalid X-Hub-Signature-256.', [
                'received' => $signature,
            ]);
            return response('Unauthorized', 403);
        }

        // ── 2. Parse and dispatch ──────────────────────────────────────────────
        $body = $request->all();

        Log::info('WhatsApp Webhook received', [
            'object' => $body['object'] ?? 'unknown',
        ]);

        if (!isset($body['entry'][0]['changes'][0]['value']['messages'][0])) {
            // Status updates, read receipts, etc. — acknowledge and ignore
            return response('OK', 200);
        }

        $messageData = $body['entry'][0]['changes'][0]['value']['messages'][0];
        $phone       = $messageData['from'];
        $type        = $messageData['type'];

        // Extract message text / button reply
        $text = match($type) {
            'text'        => $messageData['text']['body'] ?? '',
            'interactive' => $messageData['interactive']['button_reply']['id']
                ?? $messageData['interactive']['list_reply']['id']
                ?? '',
            default       => '',
        };

        // Dispatch to queue — Meta requires a 200 response within 20 seconds
        ProcessBotMessage::dispatch($phone, $text, $type, $body);

        return response('OK', 200);
    }
}