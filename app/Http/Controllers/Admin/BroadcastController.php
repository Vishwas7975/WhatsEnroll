<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Course;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    // ── Show broadcast form ───────────────────────────────────────────────────
    public function index()
    {
        $courses     = Course::where('is_active', true)->get();
        $totalActive = Enrollment::where('status', 'active')->count();

        return view('admin.broadcast.index', compact('courses', 'totalActive'));
    }

    // ── Send broadcast ────────────────────────────────────────────────────────
    public function send(Request $request)
    {
        $request->validate([
            'message'   => 'required|string|max:1000',
            'target'    => 'required|in:all,course',
            'course_id' => 'required_if:target,course|nullable|exists:courses,id',
        ]);

        // Build recipient list
        $query = Enrollment::with('user')->where('status', 'active');

        if ($request->target === 'course' && $request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        $enrollments = $query->get();

        if ($enrollments->isEmpty()) {
            return back()->with('error', 'No active enrollments found for the selected target.');
        }

        $sent   = 0;
        $failed = 0;

        foreach ($enrollments as $enrollment) {
            $phone = $enrollment->user->phone ?? null;
            if (!$phone) {
                $failed++;
                continue;
            }

            // Normalize phone: ensure it starts with country code, no + or spaces
            $phone = preg_replace('/\D/', '', $phone);
            if (!str_starts_with($phone, '91') && strlen($phone) === 10) {
                $phone = '91' . $phone;
            }

            try {
                $this->whatsapp->sendText($phone, $request->message);
                $sent++;
            } catch (\Exception $e) {
                Log::error("Broadcast failed for {$phone}: " . $e->getMessage());
                $failed++;
            }

            // Small delay to avoid Meta rate limiting
            usleep(200000); // 0.2 seconds
        }

        $summary = "Broadcast sent! ✅ {$sent} delivered" . ($failed > 0 ? ", ❌ {$failed} failed." : ".");

        return back()->with('success', $summary);
    }
}