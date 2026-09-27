@extends('admin.layout')

@section('title', 'Broadcast Messaging')

@section('content')

{{-- Header --}}
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">📢 Broadcast Messaging</h2>
    <p class="text-gray-500 text-sm mt-1">Send a WhatsApp message to all enrolled students or a specific course batch.</p>
</div>

{{-- Stats Card --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500 text-sm">Total Active Students</p>
        <h3 class="text-3xl font-bold text-blue-700">{{ $totalActive }}</h3>
        <p class="text-blue-400 text-sm mt-1"><i class="fas fa-users"></i> Will receive your message</p>
    </div>
    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500 text-sm">Total Courses</p>
        <h3 class="text-3xl font-bold text-indigo-600">{{ $courses->count() }}</h3>
        <p class="text-indigo-400 text-sm mt-1"><i class="fas fa-book"></i> Available to target</p>
    </div>
    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500 text-sm">Channel</p>
        <h3 class="text-xl font-bold text-green-600 mt-1"><i class="fab fa-whatsapp"></i> WhatsApp</h3>
        <p class="text-green-400 text-sm mt-1">Direct message delivery</p>
    </div>
</div>

{{-- Broadcast Form --}}
<div class="bg-white rounded-xl shadow p-6">
    <h3 class="font-semibold text-gray-700 mb-6 text-lg">✉️ Compose Broadcast</h3>

    <form method="POST" action="{{ route('admin.broadcast.send') }}" id="broadcastForm">
        @csrf

        {{-- Target Selection --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Send To</label>
            <div class="flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="target" value="all" checked
                           class="text-blue-600"
                           onchange="toggleCourseSelect(this.value)">
                    <span class="text-gray-700">All Enrolled Students
                        <span class="text-blue-600 font-semibold">({{ $totalActive }})</span>
                    </span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="target" value="course"
                           class="text-blue-600"
                           onchange="toggleCourseSelect(this.value)">
                    <span class="text-gray-700">Specific Course</span>
                </label>
            </div>
            @error('target')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Course Dropdown (hidden by default) --}}
        <div class="mb-6 hidden" id="courseSelectWrapper">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Course</label>
            <select name="course_id"
                    class="w-full md:w-1/2 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— Select a course —</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}"
                        {{ old('course_id') == $course->id ? 'selected' : '' }}>
                        {{ $course->name }}
                    </option>
                @endforeach
            </select>
            @error('course_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Message --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
            <textarea name="message" rows="5" maxlength="1000"
                      placeholder="Type your message here... (max 1000 characters)"
                      class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                      oninput="updateCharCount(this)">{{ old('message') }}</textarea>
            <div class="flex justify-between mt-1">
                @error('message')
                    <p class="text-red-500 text-xs">{{ $message }}</p>
                @else
                    <p class="text-gray-400 text-xs">Plain text only. Emojis supported ✅</p>
                @enderror
                <p class="text-gray-400 text-xs" id="charCount">0 / 1000</p>
            </div>
        </div>

        {{-- Preview Box --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 max-w-sm">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <i class="fab fa-whatsapp text-white text-sm"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700">{{ config('app.name', 'WhatsEnroll') }}</span>
                </div>
                <p class="text-sm text-gray-700 whitespace-pre-wrap" id="previewText">Your message will appear here...</p>
                <p class="text-xs text-gray-400 mt-2 text-right">Now</p>
            </div>
        </div>

        {{-- Warning --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 mb-6 flex items-start gap-3">
            <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5"></i>
            <p class="text-sm text-yellow-700">
                This will send a WhatsApp message to all selected students immediately.
                Make sure your message is correct before sending.
            </p>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-4">
            <button type="submit"
                    id="sendBtn"
                    onclick="return confirmSend()"
                    class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center gap-2">
                <i class="fab fa-whatsapp"></i> Send Broadcast
            </button>
            <a href="{{ route('admin.dashboard') }}"
               class="text-gray-500 text-sm hover:underline">Cancel</a>
        </div>

    </form>
</div>

@endsection

@section('scripts')
<script>
    // Toggle course dropdown
    function toggleCourseSelect(value) {
        const wrapper = document.getElementById('courseSelectWrapper');
        wrapper.classList.toggle('hidden', value !== 'course');
    }

    // Live character count + preview
    function updateCharCount(el) {
        const len = el.value.length;
        document.getElementById('charCount').textContent = len + ' / 1000';
        document.getElementById('previewText').textContent = el.value || 'Your message will appear here...';
    }

    // Initialize char count on page load if old value exists
    const msgArea = document.querySelector('textarea[name="message"]');
    if (msgArea && msgArea.value) updateCharCount(msgArea);

    // Confirm before sending
    function confirmSend() {
        const target  = document.querySelector('input[name="target"]:checked').value;
        const message = document.querySelector('textarea[name="message"]').value.trim();

        if (!message) {
            alert('Please enter a message before sending.');
            return false;
        }

        const targetLabel = target === 'all' ? 'ALL enrolled students' : 'selected course students';
        return confirm(`Are you sure you want to send this message to ${targetLabel}?`);
    }
</script>
@endsection