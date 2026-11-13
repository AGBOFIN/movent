<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\HoldController;
use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('categories', [EventController::class, 'categories']);
Route::get('events', [EventController::class, 'index']);
Route::get('events/{id}', [EventController::class, 'show'])->whereUuid('id');
Route::get('venues', [VenueController::class, 'index']);
Route::get('venues/cities', [VenueController::class, 'cities']);
Route::get('venues/{slug}', [VenueController::class, 'show'])->where('slug', '[a-z0-9-]+');

// Organisateur / Admin
Route::middleware(['jwt', 'role:ORGANIZER,ADMIN', 'throttle:60,1'])->group(function () {
    Route::post('events', [EventController::class, 'store']);
    Route::put('events/{id}', [EventController::class, 'update'])->whereUuid('id');
    Route::delete('events/{id}', [EventController::class, 'destroy'])->whereUuid('id');
    Route::get('my-events', [EventController::class, 'myEvents']);
    Route::get('my-events/{id}', [EventController::class, 'myEvent'])->whereUuid('id');
    Route::post('events/{id}/submit', [EventController::class, 'submit'])->whereUuid('id');
    Route::post('events/{id}/cancel', [EventController::class, 'cancel'])->whereUuid('id');
});

// Statistiques : organisateur (les siennes), admin et autorité nationale (global)
Route::get('event-stats', [EventController::class, 'stats'])->middleware(['jwt', 'role:ORGANIZER,ADMIN,NATIONAL_AUTHORITY']);

// Supervision nationale (lecture) + administration (modération)
Route::middleware(['jwt', 'role:ADMIN,NATIONAL_AUTHORITY', 'throttle:60,1'])->group(function () {
    Route::get('admin/events', [EventController::class, 'adminIndex']);
});
Route::middleware(['jwt', 'role:ADMIN', 'throttle:60,1'])->group(function () {
    Route::post('events/{id}/approve', [EventController::class, 'approve'])->whereUuid('id');
    Route::post('events/{id}/reject', [EventController::class, 'reject'])->whereUuid('id');
});

// Interne (Booking Service -> Event Service) ; bloqué par la gateway côté public
Route::middleware('internal')->group(function () {
    Route::post('events/{id}/hold', [HoldController::class, 'hold'])->whereUuid('id');
    Route::post('events/{id}/confirm-hold', [HoldController::class, 'confirm'])->whereUuid('id');
    Route::post('events/{id}/release-hold', [HoldController::class, 'release'])->whereUuid('id');
});
