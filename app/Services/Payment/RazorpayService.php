<?php

namespace App\Services\Payment;

use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );
    }

    // ── Create Razorpay Order (kept for reference) ────────────────────────────
    public function createOrder(int $amount, string $currency = 'INR', array $notes = []): array
    {
        $order = $this->api->order->create([
            'amount'   => $amount * 100, // paise
            'currency' => $currency,
            'notes'    => $notes,
        ]);

        return $order->toArray();
    }

    // ── Create Payment Link ───────────────────────────────────────────────────
    public function createPaymentLink(
        int    $amount,
        string $courseName,
        string $studentName,
        string $studentPhone,
        string $studentEmail,
        int    $courseId
    ): array {
        $response = $this->api->paymentLink->create([
            'amount'      => $amount * 100, // paise
            'currency'    => 'INR',
            'accept_partial' => false,
            'expire_by'   => now()->addHours(24)->timestamp,
            'description' => "Enrollment: {$courseName}",
            'customer'    => [
                'name'    => $studentName,
                'contact' => '+91' . ltrim($studentPhone, '91'),
                'email'   => $studentEmail,
            ],
            'notify' => [
                'sms'   => false,
                'email' => false,
            ],
            'reminder_enable' => false,
            'notes' => [
                'course_id'     => $courseId,
                'course_name'   => $courseName,
                'student_phone' => $studentPhone,
            ],
            'callback_url'    => config('app.url') . '/api/razorpay/webhook',
            'callback_method' => 'get',
        ]);

        return $response->toArray();
    }

    // ── Verify Webhook Signature ──────────────────────────────────────────────
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            $expectedSignature = hash_hmac(
                'sha256',
                $payload,
                config('services.razorpay.webhook_secret')
            );
            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            Log::error('Razorpay webhook verification failed: ' . $e->getMessage());
            return false;
        }
    }
}