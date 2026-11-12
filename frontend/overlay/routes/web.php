<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::view('/events', 'events');
Route::get('/events/{id}', fn (string $id) => view('event', ['id' => $id]))->where('id', '[0-9a-fA-F-]{36}');
Route::view('/login', 'login');
Route::view('/register', 'register');
Route::view('/account', 'account');
Route::view('/organizer', 'organizer');
Route::view('/admin', 'admin');
Route::view('/supervision', 'supervision');
