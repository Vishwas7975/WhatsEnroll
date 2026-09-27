@extends('admin.layout')

@section('title', 'Pending Payments')

@section('content')

<div class="bg-white rounded-xl shadow">
    <div class="px-6 py-4 border-b">
        <h3 class="font-semibold text-gray-700">⏳ Pending Payments</h3>
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
                    <th class="px-6 py-3 text-left text-gray-600">Screenshot</th>
                    <th class="px-6 py-3 text-left text-gray-600">Date</th>
                    <th class="px-6 py-3 text-left text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($payments as $payment)
                <tr>
                    <td class="px-6 py-3">{{ $payment->id }}</td>
                    <td class="px-6 py-3">{{ $payment->user->name ?? 'N/A' }}</td>
                    <td class="px-6 py-3">{{ $payment->user->phone }}</td>
                    <td class="px-6 py-3">{{ $payment->course->name ?? 'N/A' }}</td>
                    <td class="px-6 py-3">₹{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-6 py-3">
                        @if($payment->screenshot_path)
                            <a href="{{ asset('storage/' . $payment->screenshot_path) }}"
                               target="_blank"
                               class="text-blue-600 hover:underline text-xs">
                                View Screenshot
                            </a>
                        @else
                            <span class="text-gray-400 text-xs">No screenshot</span>
                        @endif
                    </td>
                    <td class="px-6 py-3">{{ $payment->created_at->format('d M Y') }}</td>
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
                    <td colspan="8" class="px-6 py-4 text-center text-gray-400">No pending payments</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4">
        {{ $payments->links() }}
    </div>
</div>

@endsection