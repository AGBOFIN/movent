<?php

use Illuminate\Support\Facades\Route;

// Le frontend ne sert aucune route API : toute requête /api/* passe par la gateway.
Route::fallback(fn () => response()->json(['message' => 'Route inconnue.'], 404));
