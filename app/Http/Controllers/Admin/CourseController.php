<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::latest()->paginate(20);
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'duration'         => 'required|string',
            'mode'             => 'required|in:online,offline,hybrid',
            'portal_course_id' => 'required|numeric',
            'portal_price'     => 'required|numeric|min:0',
        ]);

        Course::create([
            'name'             => $request->name,
            'duration'         => $request->duration,
            'mode'             => $request->mode,
            'fee'              => $request->portal_price,
            'portal_course_id' => $request->portal_course_id,
            'portal_price'     => $request->portal_price,
            'is_active'        => $request->has('is_active'),
            'translations'     => [
                'en' => $request->name,
                'hi' => $request->name_hi ?? $request->name,
                'te' => $request->name_te ?? $request->name,
            ],
        ]);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully!');
    }

    public function edit(Course $course)
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'duration'         => 'required|string',
            'mode'             => 'required|in:online,offline,hybrid',
            'portal_course_id' => 'required|numeric',
            'portal_price'     => 'required|numeric|min:0',
        ]);

        $course->update([
            'name'             => $request->name,
            'duration'         => $request->duration,
            'mode'             => $request->mode,
            'fee'              => $request->portal_price,
            'portal_course_id' => $request->portal_course_id,
            'portal_price'     => $request->portal_price,
            'is_active'        => $request->has('is_active'),
            'translations'     => [
                'en' => $request->name,
                'hi' => $request->name_hi ?? $request->name,
                'te' => $request->name_te ?? $request->name,
            ],
        ]);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course updated successfully!');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')
            ->with('success', 'Course deleted successfully!');
    }
}