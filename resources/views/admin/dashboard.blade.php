@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

{{-- Stats Row 1 --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500 text-sm">Total Students</p>
        <h3 class="text-3xl font-bold text-blue-700">{{ $stats['total_students'] }}</h3>
        <p class="text-blue-400 text-sm mt-1"><i class="fas fa-users"></i> Registered</p>
    </div>
    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500 text-sm">Total Enrollments</p>
        <h3 class="text-3xl font-bold text-green-600">{{ $stats['total_enrollments'] }}</h3>
        <p class="text-green-400 text-sm mt-1"><i class="fas fa-user-graduate"></i> Enrolled</p>
    </div>
    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500 text-sm">Pending Payments</p>
        <h3 class="text-3xl font-bold text-yellow-600">{{ $stats['pending_payments'] }}</h3>
        <p class="text-yellow-400 text-sm mt-1"><i class="fas fa-clock"></i> Awaiting</p>
    </div>
    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500 text-sm">Total Revenue</p>
        <h3 class="text-3xl font-bold text-purple-600">₹{{ number_format($stats['total_revenue'], 2) }}</h3>
        <p class="text-purple-400 text-sm mt-1"><i class="fas fa-rupee-sign"></i> Collected</p>
    </div>
</div>

{{-- Stats Row 2 --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500 text-sm">Today's Enrollments</p>
        <h3 class="text-3xl font-bold text-blue-600">{{ $stats['today_enrollments'] }}</h3>
    </div>
    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500 text-sm">Total Courses</p>
        <h3 class="text-3xl font-bold text-indigo-600">{{ $stats['total_courses'] }}</h3>
    </div>
    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500 text-sm">Completed Payments</p>
        <h3 class="text-3xl font-bold text-green-600">{{ $stats['completed_payments'] }}</h3>
    </div>
</div>

{{-- ── CHARTS ──────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

    {{-- Chart 1: Revenue by Month --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold text-gray-700 mb-4">📈 Revenue by Month (Last 6 Months)</h3>
        @if($revenueData->sum() > 0)
            <canvas id="revenueChart" height="130"></canvas>
        @else
            <div class="flex items-center justify-center h-32 text-gray-400 text-sm">
                No revenue data yet
            </div>
        @endif
    </div>

    {{-- Chart 2: Enrollments by Course --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold text-gray-700 mb-4">🎓 Enrollments by Course</h3>
        @if($courseData->sum() > 0)
            <canvas id="courseChart" height="130"></canvas>
        @else
            <div class="flex items-center justify-center h-32 text-gray-400 text-sm">
                No enrollment data yet
            </div>
        @endif
    </div>

</div>

{{-- Chart 3: Daily Trend --}}
<div class="bg-white rounded-xl shadow p-6 mb-6">
    <h3 class="font-semibold text-gray-700 mb-4">📅 Daily Enrollments — Last 30 Days</h3>
    <canvas id="trendChart" height="70"></canvas>
</div>

{{-- ── PENDING PAYMENTS ─────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow mb-6">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <h3 class="font-semibold text-gray-700">⏳ Pending Payments</h3>
        <a href="{{ route('admin.payments.pending') }}" class="text-blue-600 text-sm hover:underline">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-gray-600">Student</th>
                    <th class="px-6 py-3 text-left text-gray-600">Phone</th>
                    <th class="px-6 py-3 text-left text-gray-600">Course</th>
                    <th class="px-6 py-3 text-left text-gray-600">Amount</th>
                    <th class="px-6 py-3 text-left text-gray-600">UTR</th>
                    <th class="px-6 py-3 text-left text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pendingPayments as $payment)
                <tr>
                    <td class="px-6 py-3">{{ $payment->user->name ?? 'N/A' }}</td>
                    <td class="px-6 py-3">{{ $payment->user->phone }}</td>
                    <td class="px-6 py-3">{{ $payment->course->name ?? 'N/A' }}</td>
                    <td class="px-6 py-3">₹{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-6 py-3">
                        <span class="font-mono text-xs text-gray-600">{{ $payment->txn_id ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-3 flex gap-2">
                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                            @csrf
                            <button class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600">
                                ✅ Verify
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">
                            @csrf
                            <button class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600">
                                ❌ Reject
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-400">No pending payments</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── RECENT ENROLLMENTS ───────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <h3 class="font-semibold text-gray-700">🎓 Recent Enrollments</h3>
        <a href="{{ route('admin.enrollments.index') }}" class="text-blue-600 text-sm hover:underline">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-gray-600">Student</th>
                    <th class="px-6 py-3 text-left text-gray-600">Phone</th>
                    <th class="px-6 py-3 text-left text-gray-600">Course</th>
                    <th class="px-6 py-3 text-left text-gray-600">Status</th>
                    <th class="px-6 py-3 text-left text-gray-600">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentEnrollments as $enrollment)
                <tr>
                    <td class="px-6 py-3">{{ $enrollment->user->name ?? 'N/A' }}</td>
                    <td class="px-6 py-3">{{ $enrollment->user->phone }}</td>
                    <td class="px-6 py-3">{{ $enrollment->course->name ?? 'N/A' }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 rounded-full text-xs
                            {{ $enrollment->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($enrollment->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-3">{{ $enrollment->enrolled_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">No enrollments yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // ── Chart 1: Revenue by Month ─────────────────────────────────────────────
    @if($revenueData->sum() > 0)
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: @json($revenueLabels),
            datasets: [{
                label: 'Revenue (₹)',
                data: @json($revenueData),
                backgroundColor: 'rgba(99, 102, 241, 0.75)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => '₹' + Number(ctx.parsed.y).toLocaleString('en-IN')
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: val => '₹' + Number(val).toLocaleString('en-IN')
                    }
                }
            }
        }
    });
    @endif

    // ── Chart 2: Enrollments by Course ───────────────────────────────────────
    @if($courseData->sum() > 0)
    new Chart(document.getElementById('courseChart'), {
        type: 'doughnut',
        data: {
            labels: @json($courseLabels),
            datasets: [{
                data: @json($courseData),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.85)',
                    'rgba(16, 185, 129, 0.85)',
                    'rgba(245, 158, 11, 0.85)',
                    'rgba(239, 68, 68, 0.85)',
                    'rgba(139, 92, 246, 0.85)',
                    'rgba(236, 72, 153, 0.85)',
                ],
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 12 }, padding: 16 }
                }
            }
        }
    });
    @endif

    // ── Chart 3: Daily Enrollments Trend ─────────────────────────────────────
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: @json($trendLabels),
            datasets: [{
                label: 'Enrollments',
                data: @json($trendData),
                borderColor: 'rgba(16, 185, 129, 1)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                fill: true,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                },
                x: {
                    ticks: { maxTicksLimit: 10, maxRotation: 45 }
                }
            }
        }
    });
</script>
@endsection