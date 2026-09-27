<?php

namespace App\Services\Credential;

use App\Mail\EnrollmentConfirmation;
use App\Models\WhatsappUser;
use App\Models\Enrollment;
use App\Models\Credential;
use App\Models\Course;
use App\Services\WhatsApp\WhatsAppService;
use App\Services\Portal\PortalService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CredentialService
{
    protected WhatsAppService $whatsapp;
    protected PortalService $portal;

    public function __construct(
        WhatsAppService $whatsapp,
        PortalService $portal
    ) {
        $this->whatsapp = $whatsapp;
        $this->portal   = $portal;
    }

    public function enrollAndDeliver(
        WhatsappUser $user,
        int $courseId,
        float $amount,
        string $utrNumber,
        string $paymentType = 'full'
    ): bool {
        try {
            $course = Course::find($courseId);

            if (!$course) {
                Log::error('Course not found: ' . $courseId);
                return false;
            }

            $portalCourseId = $course->portal_course_id ?? $courseId;
            $portalPrice    = $course->portal_price ?? $amount;

            // Call portal API
            $result = $this->portal->enrollStudent([
                'name'         => $user->name ?? 'Student',
                'email'        => $user->email ?? ($user->phone . '@placeholder.invalid'),
                'phone'        => $user->phone,
                'course_id'    => $portalCourseId,
                'course_title' => $course->name,
                'amount'       => $portalPrice,
                'utr_number'   => $utrNumber,
                'payment_type' => $paymentType,
            ]);

            if (!$result['success']) {
                Log::error('Portal enrollment failed: ' . $result['message']);
                $this->whatsapp->sendText(
                    $user->phone,
                    "🎉 Your enrollment is confirmed for *{$course->name}*!\n\nOur team will send your login credentials shortly."
                );
                return false;
            }

            $data = $result['data'];

            // Save credentials in the local DB
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->latest()
                ->first();

            if ($enrollment) {
                Credential::updateOrCreate(
                    ['user_id' => $user->id, 'enrollment_id' => $enrollment->id],
                    [
                        'portal_username'      => $data['username'],
                        'portal_password_hash' => bcrypt($data['password'] ?? ''),
                        'delivered_at'         => now(),
                    ]
                );
            }

            // Send WhatsApp credentials
            $this->sendCredentialsWhatsApp($user, $data);

            // Send Email credentials
            $this->sendCredentialsEmail($user, $data, $course, $amount);

            return true;

        } catch (\Exception $e) {
            Log::error('CredentialService error: ' . $e->getMessage());
            return false;
        }
    }

    protected function sendCredentialsWhatsApp(WhatsappUser $user, array $data): void
    {
        $isNew = $data['is_new_user'] ?? false;

        if ($isNew) {
            $message = "🎉 *Enrollment Confirmed!*\n\n"
                . "Course: *{$data['course_title']}*\n\n"
                . "Your portal login credentials:\n"
                . "🌐 Portal: {$data['portal_url']}\n"
                . "📧 Username: *{$data['username']}*\n"
                . "🔑 Password: *{$data['password']}*\n\n"
                . "Please login and change your password after first login.\n\n"
                . "Welcome to WhatsEnroll! 🚀";
        } else {
            $message = "🎉 *Enrollment Confirmed!*\n\n"
                . "Course: *{$data['course_title']}*\n\n"
                . "You have been enrolled successfully!\n"
                . "Login to your existing account at:\n"
                . "🌐 {$data['portal_url']}\n\n"
                . "Welcome back to WhatsEnroll! 🚀";
        }

        $this->whatsapp->sendText($user->phone, $message);
    }

    protected function sendCredentialsEmail(
        WhatsappUser $user,
        array $data,
        Course $course,
        float $amount
    ): void {
        try {
            $email = $user->email ?? $data['email'] ?? null;

            if (!$email) {
                Log::warning('No email for user: ' . $user->phone);
                return;
            }

            Mail::to($email)->send(new EnrollmentConfirmation(
                $user->name ?? 'Student',
                $course->name,
                $data['username'],
                $data['password'] ?? '',
                $data['portal_url'],
                $amount,
                $data['is_new_user'] ?? false
            ));
            Log::info('Enrollment email sent to: ' . $email);

        } catch (\Exception $e) {
            Log::error('Email delivery failed: ' . $e->getMessage());
        }
    }
}