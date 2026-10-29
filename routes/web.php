<?php

use App\Http\Controllers\BadgeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('oefeningen', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::get('badges', [BadgeController::class, 'index'])->name('badges.index');
});

require __DIR__.'/auth.php';
