<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('consulta', function () {
        return Inertia::render('consulta/page', [
            'user' => auth()->user(),
        ]);
    })->name('consulta');

    Route::get('consulta/history', function () {
        return Inertia::render('consulta/history', [
            'user' => auth()->user(),
        ]);
    })->name('consulta.history');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
