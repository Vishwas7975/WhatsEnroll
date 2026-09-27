<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DeliverCredentials;
use App\Models\AdminLog;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        $enrollments = Enrollment::with(['user', 'course', 'payment'])
            ->latest()
            ->paginate(20);

        return view('admin.enrollments.index', compact('enrollments'));
    }

    public function pendingPayments()
    {
        $payments = Payment::with(['user', 'course'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.payments.pending', compact('payments'));
    }

    public function verifyPayment(Payment $payment)
    {
        // 1. Mark payment as completed
        $payment->update(['status' => 'completed']);

        // 2. Create enrollment record if not already exists
        Enrollment::firstOrCreate(
            ['user_id' => $payment->user_id, 'course_id' => $payment->course_id],
            [
                'payment_id'  => $payment->id,
                'status'      => 'active',
                'enrolled_at' => now(),
            ]
        );

        // 3. Notify student immediately on WhatsApp
        $this->whatsapp->sendText(
            $payment->user->phone,
            "✅ *Payment Verified!*\n\n"
            . "Your payment of *Rs.{$payment->amount}* has been confirmed.\n"
            . "We're now setting up your course access — credentials will arrive in a few minutes! 🚀"
        );

        // 4. Dispatch credential delivery to queue (async via Horizon)
        DeliverCredentials::dispatch(
            $payment->user,
            $payment->course_id,
            $payment->amount,
            $payment->txn_id ?? 'MANUAL_' . time(),
            'full'
        );

        Log::info('Admin verified payment, credentials queued', [
            'payment_id' => $payment->id,
            'user_id'    => $payment->user_id,
        ]);

        // Log to admin_logs table for audit trail
        AdminLog::create([
            'admin_id'     => Auth::id(),
            'action'       => 'payment_verified',
            'subject_type' => Payment::class,
            'subject_id'   => $payment->id,
            'properties'   => [
                'user_id'    => $payment->user_id,
                'course_id'  => $payment->course_id,
                'amount'     => $payment->amount,
                'txn_id'     => $payment->txn_id,
            ],
        ]);

        return back()->with('success', 'Payment verified! Credentials are being delivered to the student.');
    }

    public function rejectPayment(Payment $payment)
    {
        $payment->update(['status' => 'failed']);

        // Notify student -- use a generic support contact from config/app.php (APP_SUPPORT_CONTACT)
        $supportContact = config('app.support_contact', 'the admin team');
        $this->whatsapp->sendText(
            $payment->user->phone,
            "Your payment could not be verified.\n\n"
            . "If you believe this is an error, please contact: {$supportContact}\n\n"
            . "You can also send a fresh UTR by messaging us again."
        );

        // Log to admin_logs table for audit trail
        AdminLog::create([
            'admin_id'     => Auth::id(),
            'action'       => 'payment_rejected',
            'subject_type' => Payment::class,
            'subject_id'   => $payment->id,
            'properties'   => [
                'user_id'   => $payment->user_id,
                'course_id' => $payment->course_id,
                'amount'    => $payment->amount,
            ],
        ]);

        return back()->with('error', 'Payment rejected and student notified.');
    }
}