@extends('admin.layout')

@section('title', 'Enrollments')

@section('content')

<div class="bg-white rounded-xl shadow">
    <div class="px-6 py-4 border-b">
        <h3 class="font-semibold text-gray-700">🎓 All Enrollments</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-gray-600">#</th>
                    <th class="px-6 py-3 text-left text-gray-600">Student</th>
                    <th class="px-6 py-3 text-left text-gray-600">Phone</th>
                    <th class="px-6 py-3 text-left text-gray-600">Course</th>
                    <th class="px-6 py-3 text-left text-gray-600">Amount</th>
                    <th class="px-6 py-3 text-left text-gray-600">Status</th>
                    <th class="px-6 py-3 text-left text-gray-600">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($enrollments as $enrollment)
                <tr>
                    <td class="px-6 py-3">{{ $enrollment->id }}</td>
                    <td class="px-6 py-3">{{ $enrollment->user->name ?? 'N/A' }}</td>
                    <td class="px-6 py-3">{{ $enrollment->user->phone }}</td>
                    <td class="px-6 py-3">{{ $enrollment->course->name ?? 'N/A' }}</td>
                    <td class="px-6 py-3">₹{{ number_format($enrollment->payment->amount ?? 0, 2) }}</td>
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
                    <td colspan="7" class="px-6 py-4 text-center text-gray-400">No enrollments yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4">
        {{ $enrollments->links() }}
    </div>
</div>

@endsection