<?php

namespace App\Services\Bot;

use App\Models\WhatsappUser;
use App\Models\Course;
use App\Models\Payment;
use App\Models\ChatHistory;
use App\Services\WhatsApp\WhatsAppService;
use App\Services\Payment\RazorpayService;
use Illuminate\Support\Facades\Log;

class BotFlowService
{
    protected WhatsAppService $whatsapp;
    protected SessionService $session;
    protected LanguageService $language;
    protected RazorpayService $razorpay;

    public function __construct(
        WhatsAppService $whatsapp,
        SessionService $session,
        LanguageService $language,
        RazorpayService $razorpay
    ) {
        $this->whatsapp = $whatsapp;
        $this->session  = $session;
        $this->language = $language;
        $this->razorpay = $razorpay;
    }

    public function handle(WhatsappUser $user, string $text, string $type): void
    {
        $state = $user->session_state ?? 'IDLE';

        Log::info("BotFlow: user={$user->phone} state={$state} text={$text}");

        // Allow reset keywords from any state
        if (in_array(strtolower(trim($text)), ['menu', 'hi', 'hello', 'start', 'reset'])) {
            $user->update(['session_state' => 'IDLE']);
            $state = 'IDLE';
        }

        match($state) {
            'IDLE'            => $this->handleIdle($user, $text),
            'LANGUAGE_SELECT' => $this->handleLanguageSelect($user, $text),
            'MAIN_MENU'       => $this->handleMainMenu($user, $text),
            'COURSE_LIST'     => $this->handleCourseList($user, $text),
            'COLLECT_NAME'    => $this->handleCollectName($user, $text),
            'COLLECT_EMAIL'   => $this->handleCollectEmail($user, $text),
            'PAYMENT_PENDING' => $this->handlePaymentPending($user, $text),
            default           => $this->handleIdle($user, $text),
        };
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    protected function send(WhatsappUser $user, string $message, string $type = 'text'): void
    {
        ChatHistory::create([
            'user_id'      => $user->id,
            'direction'    => 'outbound',
            'message_type' => $type,
            'body'         => $message,
        ]);

        $this->whatsapp->sendText($user->phone, $message);
    }

    protected function sendButtons(WhatsappUser $user, string $body, array $buttons): void
    {
        ChatHistory::create([
            'user_id'      => $user->id,
            'direction'    => 'outbound',
            'message_type' => 'interactive_buttons',
            'body'         => $body,
        ]);

        $this->whatsapp->sendButtons($user->phone, $body, $buttons);
    }

    protected function sendList(WhatsappUser $user, string $body, string $buttonText, array $sections): void
    {
        ChatHistory::create([
            'user_id'      => $user->id,
            'direction'    => 'outbound',
            'message_type' => 'interactive_list',
            'body'         => $body,
        ]);

        $this->whatsapp->sendList($user->phone, $body, $buttonText, $sections);
    }

    // ─── States ─────────────────────────────────────────────────────────────

    protected function handleIdle(WhatsappUser $user, string $text): void
    {
        $this->sendButtons(
            $user,
            "🎓 *Welcome to " . config('app.name', 'WhatsEnroll') . "!*\n\nYour gateway to professional courses & internships.\n\nPlease select your language:",
            [
                ['id' => 'lang_en', 'title' => 'English'],
                ['id' => 'lang_hi', 'title' => 'हिंदी'],
                ['id' => 'lang_te', 'title' => 'తెలుగు'],
            ]
        );
        $user->update(['session_state' => 'LANGUAGE_SELECT']);
    }

    protected function handleLanguageSelect(WhatsappUser $user, string $text): void
    {
        $langMap = [
            'lang_en' => 'en',
            'lang_hi' => 'hi',
            'lang_te' => 'te',
        ];

        $lang = $langMap[$text] ?? 'en';
        $user->update(['preferred_lang' => $lang, 'session_state' => 'MAIN_MENU']);

        $this->sendButtons(
            $user,
            $this->language->get('main_menu', $lang),
            [
                ['id' => 'menu_courses',     'title' => '📚 Courses'],
                ['id' => 'menu_internships', 'title' => '💼 Internships'],
                ['id' => 'menu_support',     'title' => '🆘 Support'],
            ]
        );
    }

    protected function handleMainMenu(WhatsappUser $user, string $text): void
    {
        $lang = $user->preferred_lang ?? 'en';

        match($text) {
            'menu_courses'     => $this->showCourses($user, $lang),
            'menu_internships' => $this->send($user, "💼 Internship programs coming soon! Stay tuned.\n\nType *menu* to go back."),
            'menu_support'     => $this->send($user, "🆘 *Support*\n\nEmail: " . config('app.support_email', 'support@example.com') . "\nPortal: " . config('services.portal.api_url', config('app.url')) . "\n\nType *menu* to go back."),
            default            => $this->sendButtons(
                $user,
                $this->language->get('invalid_option', $lang) . "\n\n" . $this->language->get('main_menu', $lang),
                [
                    ['id' => 'menu_courses',     'title' => '📚 Courses'],
                    ['id' => 'menu_internships', 'title' => '💼 Internships'],
                    ['id' => 'menu_support',     'title' => '🆘 Support'],
                ]
            ),
        };
    }

    protected function showCourses(WhatsappUser $user, string $lang): void
    {
        $courses = Course::where('is_active', true)->get();

        if ($courses->isEmpty()) {
            $this->send($user, "No courses available at the moment. Check back soon!\n\nType *menu* to go back.");
            return;
        }

        $rows = $courses->map(fn($course) => [
            'id'          => 'course_' . $course->id,
            'title'       => mb_substr($course->translations[$lang] ?? $course->name, 0, 24),
            'description' => "Duration: {$course->duration} | Rs.{$course->fee}",
        ])->toArray();

        $this->sendList(
            $user,
            $this->language->get('courses', $lang),
            'View Courses',
            [['title' => 'Available Courses', 'rows' => $rows]]
        );

        $user->update(['session_state' => 'COURSE_LIST']);
    }

    protected function handleCourseList(WhatsappUser $user, string $text): void
    {
        $lang = $user->preferred_lang ?? 'en';

        if (str_starts_with($text, 'course_')) {
            $courseId = (int) str_replace('course_', '', $text);
            $course   = Course::find($courseId);

            if (!$course) {
                $this->send($user, "Course not found. Please try again.");
                return;
            }

            $courseName = $course->translations[$lang] ?? $course->name;

            $this->sendButtons(
                $user,
                "📚 *{$courseName}*\n\n"
                . "⏱ Duration: {$course->duration}\n"
                . "💻 Mode: {$course->mode}\n"
                . "💰 Fee: Rs.{$course->fee}\n\n"
                . "Would you like to enroll?",
                [
                    ['id' => 'enroll_' . $course->id, 'title' => '✅ Enroll Now'],
                    ['id' => 'menu_courses',           'title' => '🔙 Back'],
                ]
            );

            $this->session->setValue($user, 'selected_course_id', $course->id);

        } elseif (str_starts_with($text, 'enroll_')) {
            $courseId = (int) str_replace('enroll_', '', $text);
            $this->session->setValue($user, 'selected_course_id', $courseId);
            $this->askName($user);

        } else {
            $this->showCourses($user, $lang);
        }
    }

    protected function askName(WhatsappUser $user): void
    {
        $this->send(
            $user,
            "Great choice! 🎉\n\nPlease enter your *full name* to proceed with enrollment:"
        );
        $user->update(['session_state' => 'COLLECT_NAME']);
    }

    protected function handleCollectName(WhatsappUser $user, string $text): void
    {
        $text = trim($text);

        if (strlen($text) < 3 || !preg_match('/\s+/', $text)) {
            $this->send($user, "Please enter your *full name* (first and last name).\nExample: Ravi Kumar");
            return;
        }

        $user->update(['name' => $text]);
        $this->session->setValue($user, 'student_name', $text);

        $this->send(
            $user,
            "Thank you, *{$text}*! 👋\n\nPlease enter your *email address* to receive your login credentials:"
        );
        $user->update(['session_state' => 'COLLECT_EMAIL']);
    }

    protected function handleCollectEmail(WhatsappUser $user, string $text): void
    {
        if (!filter_var(trim($text), FILTER_VALIDATE_EMAIL)) {
            $this->send($user, "Please enter a valid email address.\nExample: yourname@gmail.com");
            return;
        }

        $email = strtolower(trim($text));
        $user->update(['email' => $email]);
        $this->session->setValue($user, 'student_email', $email);

        $courseId = $this->session->getValue($user, 'selected_course_id');
        $course   = Course::find($courseId);

        if (!$course) {
            $this->send($user, "Something went wrong. Please type *menu* to start again.");
            $user->update(['session_state' => 'IDLE']);
            return;
        }

        try {
            // ── Create Razorpay Payment Link ──────────────────────────────────
            $paymentLink = $this->razorpay->createPaymentLink(
                (int) $course->fee,
                $course->name,
                $user->name ?? 'Student',
                $user->phone,
                $email,
                $course->id
            );

            $linkUrl = $paymentLink['short_url'] ?? $paymentLink['payment_link_url'] ?? null;

            // ── Save payment record ───────────────────────────────────────────
            Payment::create([
                'user_id'                => $user->id,
                'course_id'              => $course->id,
                'razorpay_order_id'      => $paymentLink['id'],
                'amount'                 => $course->fee,
                'status'                 => 'pending',
            ]);

            $this->session->setValue($user, 'razorpay_order_id', $paymentLink['id']);

            // ── Send payment link to student ──────────────────────────────────
            $this->send(
                $user,
                "💳 *Complete Your Payment*\n\n"
                . "Course: *{$course->name}*\n"
                . "Amount: *₹{$course->fee}*\n\n"
                . "━━━━━━━━━━━━━━━━━━\n"
                . "🔗 *Click below to pay securely:*\n"
                . "{$linkUrl}\n\n"
                . "✅ Supports UPI, Cards, Net Banking\n"
                . "🔒 Secured by Razorpay\n\n"
                . "_Your credentials will be delivered automatically after payment. No manual steps needed!_ 🎉\n\n"
                . "_Link expires in 24 hours._"
            );

            $user->update(['session_state' => 'PAYMENT_PENDING']);

        } catch (\Exception $e) {
            Log::error('Razorpay payment link creation failed: ' . $e->getMessage());
            $this->send($user, "Payment setup failed. Please try again or contact " . config('app.support_email', 'support@example.com') . "");
        }
    }

    protected function handlePaymentPending(WhatsappUser $user, string $text): void
    {
        // Payment is now handled automatically via Razorpay webhook
        // Just reassure the student if they message during pending state

        $lower = strtolower(trim($text));

        if (in_array($lower, ['paid', 'done', 'completed', 'payment done', 'i paid'])) {
            $this->send(
                $user,
                "✅ Thank you! We're verifying your payment.\n\n"
                . "Your credentials will be sent to your WhatsApp & email automatically within a few minutes.\n\n"
                . "Questions? " . config('app.support_email', 'support@example.com') . " 🙏"
            );
        } else {
            $this->send(
                $user,
                "⏳ *Payment Pending*\n\n"
                . "Please complete your payment using the link we sent.\n\n"
                . "Once paid, your credentials will be delivered automatically. 🎉\n\n"
                . "Type *menu* to go back to main menu."
            );
        }
    }
}