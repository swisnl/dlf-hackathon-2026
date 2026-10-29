<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
});

require __DIR__.'/auth.php';
