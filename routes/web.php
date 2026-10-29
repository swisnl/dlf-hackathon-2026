<?php

use App\Http\Controllers\BadgeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('oefeningen', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::get('badges', [BadgeController::class, 'index'])->name('badges.index');
});

// Not behind "verified", so a student can still fix a mistyped new email address.
Route::middleware('auth')->group(function (): void {
    Route::get('profiel', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profiel', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profiel/wachtwoord', [PasswordController::class, 'update'])->name('profile.password');
});

Route::inertia('privacy', 'Privacy')->name('privacy');

require __DIR__.'/auth.php';
