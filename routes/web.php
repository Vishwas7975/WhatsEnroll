<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root to admin
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Admin Routes — require authentication AND admin/super-admin role
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin|super-admin'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Enrollments
    Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');

    // Payments
    Route::get('/payments/pending', [EnrollmentController::class, 'pendingPayments'])->name('payments.pending');
    Route::post('/payments/{payment}/verify', [EnrollmentController::class, 'verifyPayment'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [EnrollmentController::class, 'rejectPayment'])->name('payments.reject');

    // Courses
    Route::resource('courses', CourseController::class);

    // Broadcast
    Route::get('/broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
    Route::post('/broadcast/send', [BroadcastController::class, 'send'])->name('broadcast.send');

});

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';