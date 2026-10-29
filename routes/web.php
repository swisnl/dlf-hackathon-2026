<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ExerciseMessageController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:10,1');
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::post('submissions', [SubmissionController::class, 'store'])->name('submissions.store')->middleware('throttle:ai');
    Route::get('submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
    Route::post('submissions/{submission}/exercises', [ExerciseController::class, 'store'])->name('exercises.store')->middleware('throttle:ai');

    Route::get('exercises/{exercise}', [ExerciseController::class, 'show'])->name('exercises.show');
    Route::post('exercises/{exercise}/answer', [ExerciseController::class, 'answer'])->name('exercises.answer')->middleware('throttle:ai');
    Route::post('exercises/{exercise}/messages', [ExerciseMessageController::class, 'store'])->name('exercises.messages.store')->middleware('throttle:ai');
});

Route::inertia('privacy', 'Privacy')->name('privacy');

require __DIR__.'/auth.php';
