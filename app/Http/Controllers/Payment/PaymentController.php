<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Jobs\DeliverCredentials;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\Payment\RazorpayService;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected RazorpayService $razorpay;
    protected WhatsAppService $whatsapp;

    public function __construct(
        RazorpayService $razorpay,
        WhatsAppService $whatsapp
    ) {
        $this->razorpay = $razorpay;
        $this->whatsapp = $whatsapp;
    }

    // Handle Razorpay webhook (automatic online payments)
    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        if (!$this->razorpay->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Invalid Razorpay webhook signature');
            return response('Unauthorized', 403);
        }

        $data  = $request->all();
        $event = $data['event'] ?? '';

        Log::info('Razorpay Webhook Event: ' . $event);

        if ($event === 'payment.captured') {
            $this->handlePaymentCaptured($data);
        }

        return response('OK', 200);
    }

    protected function handlePaymentCaptured(array $data): void
    {
        $paymentData = $data['payload']['payment']['entity'] ?? [];
        $orderId     = $paymentData['order_id'] ?? null;
        $paymentId   = $paymentData['id'] ?? null;

        if (!$orderId) return;

        $payment = Payment::where('razorpay_order_id', $orderId)->first();

        if (!$payment) {
            Log::warning('Payment not found for Razorpay order: ' . $orderId);
            return;
        }

        // Mark payment completed
        $payment->update([
            'status'              => 'completed',
            'razorpay_payment_id' => $paymentId,
        ]);

        // Create enrollment record
        Enrollment::firstOrCreate(
            ['user_id' => $payment->user_id, 'course_id' => $payment->course_id],
            [
                'payment_id'  => $payment->id,
                'status'      => 'active',
                'enrolled_at' => now(),
            ]
        );

        // Queue credential delivery
        DeliverCredentials::dispatch(
            $payment->user,
            $payment->course_id,
            $payment->amount,
            $paymentId ?? 'RAZORPAY_' . time(),
            'full'
        );

        Log::info('Razorpay payment captured, credentials queued', [
            'order_id'   => $orderId,
            'payment_id' => $paymentId,
        ]);
    }
}