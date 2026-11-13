<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\OrganizerController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function () {
    Route::get('bookings', [BookingController::class, 'index']);
    Route::get('bookings/{id}', [BookingController::class, 'show'])->whereUuid('id');

    // Écritures réservées aux rôles « clients » : l'autorité nationale est en lecture seule
    Route::middleware(['role:PARTICIPANT,ORGANIZER,ADMIN', 'throttle:30,1'])->group(function () {
        Route::post('bookings', [BookingController::class, 'store']);
        Route::post('bookings/{id}/pay', [BookingController::class, 'pay'])->whereUuid('id');
        Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel'])->whereUuid('id');
    });

    Route::get('tickets', [TicketController::class, 'index']);
    Route::get('tickets/{id}', [TicketController::class, 'show'])->whereUuid('id');
    Route::get('tickets/{id}/download', [TicketController::class, 'download'])->whereUuid('id');

    // Lecture : organisateur (les siennes), admin et autorité nationale (toutes)
    Route::middleware(['role:ORGANIZER,ADMIN,NATIONAL_AUTHORITY', 'throttle:60,1'])->group(function () {
        Route::get('organizer/bookings', [OrganizerController::class, 'bookings']);
        Route::get('booking-stats', [OrganizerController::class, 'stats']);
    });
});
