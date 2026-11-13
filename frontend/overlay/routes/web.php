<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::view('/events', 'events');
Route::get('/events/{id}', fn (string $id) => view('event', ['id' => $id]))->where('id', '[0-9a-fA-F-]{36}');
Route::view('/lieux', 'venues');
Route::get('/lieux/{slug}', fn (string $slug) => view('venue', ['slug' => $slug]))->where('slug', '[a-z0-9-]+');
Route::view('/login', 'login');
Route::view('/register', 'register');
Route::view('/account', 'account');
Route::view('/organizer', 'organizer');
Route::view('/admin', 'admin');
Route::view('/supervision', 'supervision');
Route::view('/paiement-reussi', 'payment-success');

Route::view('/a-propos', 'pages.a-propos');
Route::view('/contact', 'pages.contact');
Route::view('/faq', 'pages.faq');
Route::view('/mentions-legales', 'pages.mentions-legales');
Route::view('/conditions', 'pages.conditions');
Route::view('/confidentialite', 'pages.confidentialite');
