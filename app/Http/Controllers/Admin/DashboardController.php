<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\WhatsappUser;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Stats Cards ───────────────────────────────────────────────────────
        $stats = [
            'total_students'     => WhatsappUser::count(),
            'total_enrollments'  => Enrollment::count(),
            'total_courses'      => Course::count(),
            'pending_payments'   => Payment::where('status', 'pending')->count(),
            'completed_payments' => Payment::where('status', 'completed')->count(),
            'today_enrollments'  => Enrollment::whereDate('enrolled_at', today())->count(),
            'total_revenue'      => Payment::where('status', 'completed')->sum('amount'),
        ];

        $recentEnrollments = Enrollment::with(['user', 'course'])
            ->latest()
            ->take(10)
            ->get();

        $pendingPayments = Payment::with(['user', 'course'])
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        // ── Chart 1: Revenue by month (last 6 months) ─────────────────────────
        $revenueByMonth = Payment::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%b %Y') as month"),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as sort_key"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month', 'sort_key')
            ->orderBy('sort_key')
            ->get();

        $revenueLabels = $revenueByMonth->pluck('month');
        $revenueData   = $revenueByMonth->pluck('total');

        // ── Chart 2: Enrollments by course ────────────────────────────────────
        $enrollmentsByCourse = Course::withCount(['enrollments' => fn($q) =>
            $q->where('status', 'active')
        ])->orderByDesc('enrollments_count')->get();

        $courseLabels = $enrollmentsByCourse->pluck('name');
        $courseData   = $enrollmentsByCourse->pluck('enrollments_count');

        // ── Chart 3: Daily enrollments trend — last 30 days ───────────────────
        $rawTrend = Enrollment::where('enrolled_at', '>=', now()->subDays(29)->startOfDay())
            ->select(
                DB::raw('DATE(enrolled_at) as day'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $trendLabels = [];
        $trendData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $date          = now()->subDays($i)->format('Y-m-d');
            $trendLabels[] = now()->subDays($i)->format('d M');
            $trendData[]   = $rawTrend[$date]->count ?? 0;
        }

        return view('admin.dashboard', compact(
            'stats',
            'recentEnrollments',
            'pendingPayments',
            'revenueLabels', 'revenueData',
            'courseLabels',  'courseData',
            'trendLabels',   'trendData'
        ));
    }
}