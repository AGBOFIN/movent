@extends('layouts.app')
@section('title', 'Espace organisateur')
@section('page', 'organizer')
@section('content')
<div class="between"><h1 class="h1">Espace organisateur</h1><button id="new" class="btn">+ Nouvel événement</button></div>
<div id="stats" class="stats"></div>
<div class="tabs" id="tabs"><button data-t="events" class="on">Mes événements</button><button data-t="bookings">Réservations</button></div>
<section id="t-events"></section>
<section id="t-bookings" hidden></section>
@endsection
