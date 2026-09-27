@extends('admin.layout')

@section('title', 'Courses')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h3 class="font-semibold text-gray-700">📚 All Courses</h3>
    <a href="{{ route('admin.courses.create') }}"
       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
        + Add Course
    </a>
</div>

<div class="bg-white rounded-xl shadow">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-gray-600">#</th>
                    <th class="px-6 py-3 text-left text-gray-600">Name</th>
                    <th class="px-6 py-3 text-left text-gray-600">Duration</th>
                    <th class="px-6 py-3 text-left text-gray-600">Mode</th>
                    <th class="px-6 py-3 text-left text-gray-600">Fee</th>
                    <th class="px-6 py-3 text-left text-gray-600">Status</th>
                    <th class="px-6 py-3 text-left text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($courses as $course)
                <tr>
                    <td class="px-6 py-3">{{ $course->id }}</td>
                    <td class="px-6 py-3 font-medium">{{ $course->name }}</td>
                    <td class="px-6 py-3">{{ $course->duration }}</td>
                    <td class="px-6 py-3">{{ ucfirst($course->mode) }}</td>
                    <td class="px-6 py-3">₹{{ number_format($course->fee, 2) }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 rounded-full text-xs
                            {{ $course->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $course->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-3 flex gap-2">
                        <a href="{{ route('admin.courses.edit', $course) }}"
                           class="bg-yellow-500 text-white px-3 py-1 rounded text-xs hover:bg-yellow-600">
                            ✏️ Edit
                        </a>
                        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Are you sure?')"
                                    class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600">
                                🗑️ Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-400">No courses yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4">
        {{ $courses->links() }}
    </div>
</div>

@endsection