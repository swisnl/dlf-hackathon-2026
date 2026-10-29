<?php

use App\Http\Controllers\BadgeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ExerciseMessageController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\WijsjeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::get('badges', [BadgeController::class, 'index'])->name('badges.index');

    Route::get('submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::post('submissions', [SubmissionController::class, 'store'])->name('submissions.store')->middleware('throttle:ai');
    Route::get('submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
    Route::post('submissions/{submission}/exercises', [ExerciseController::class, 'store'])->name('exercises.store')->middleware('throttle:ai');

    Route::get('exercises/{exercise}', [ExerciseController::class, 'show'])->name('exercises.show');
    Route::post('exercises/{exercise}/answer', [ExerciseController::class, 'answer'])->name('exercises.answer')->middleware('throttle:ai');
    Route::post('exercises/{exercise}/messages', [ExerciseMessageController::class, 'store'])->name('exercises.messages.store')->middleware('throttle:ai');

    Route::post('wijsje', WijsjeController::class)->name('wijsje')->middleware('throttle:ai');
});

// Not behind "verified", so a student can still fix a mistyped new email address.
Route::middleware('auth')->group(function (): void {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [PasswordController::class, 'update'])->name('profile.password');
});

Route::inertia('privacy', 'Privacy')->name('privacy');

require __DIR__.'/auth.php';
