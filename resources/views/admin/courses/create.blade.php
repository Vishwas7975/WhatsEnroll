@extends('admin.layout')

@section('title', 'Add Course')

@section('content')

<div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-6">
    <h3 class="font-semibold text-gray-700 mb-6">➕ Add New Course</h3>

    <form method="POST" action="{{ route('admin.courses.store') }}">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Course Name (English)</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g. Java Full Stack">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Course Name (Hindi)</label>
            <input type="text" name="name_hi" value="{{ old('name_hi') }}"
                   class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g. जावा फुल स्टैक">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Course Name (Telugu)</label>
            <input type="text" name="name_te" value="{{ old('name_te') }}"
                   class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g. జావా ఫుల్ స్టాక్">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
            <input type="text" name="duration" value="{{ old('duration') }}"
                   class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g. 3 Months">
            @error('duration') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
            <select name="mode" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="online">Online</option>
                <option value="offline">Offline</option>
                <option value="hybrid">Hybrid</option>
            </select>
        </div>

        <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <h4 class="font-medium text-blue-700 mb-3">🔗 Portal Mapping</h4>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Portal Course ID</label>
                <input type="number" name="portal_course_id" value="{{ old('portal_course_id') }}"
                       class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Course ID from your external LMS/portal e.g. 5">
                <p class="text-xs text-gray-500 mt-1">Find this in your LMS portal database → courses table → id column</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Course Price (₹)</label>
                <input type="number" name="portal_price" value="{{ old('portal_price') }}"
                       class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Actual price in LMS portal e.g. 2999">
                <p class="text-xs text-gray-500 mt-1">This is the price stored in your LMS portal payments table</p>
            </div>
        </div>

        <div class="mb-6 flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" checked>
            <label for="is_active" class="text-sm text-gray-700">Active</label>
        </div>

        <div class="flex gap-4">
            <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Save Course
            </button>
            <a href="{{ route('admin.courses.index') }}"
               class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">
                Cancel
            </a>
        </div>
    </form>
</div>

@endsection