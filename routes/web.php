<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/', function () {
        return Inertia::render('Home');
    })->name('dashboard');
});

require __DIR__.'/auth.php';
