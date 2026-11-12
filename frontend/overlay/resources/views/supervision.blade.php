@extends('layouts.app')
@section('title', 'Supervision nationale')
@section('page', 'supervision')
@section('content')
<h1 class="h1">Supervision nationale</h1>
<p class="muted">Vue consolidée des activités événementielles sur le territoire — accès en lecture seule.</p>
<div id="stats" class="stats"></div>
<div class="tabs" id="tabs">
  <button data-t="events" class="on">Événements</button><button data-t="users">Utilisateurs</button><button data-t="bookings">Réservations</button>
</div>
<section id="t-events"></section>
<section id="t-users" hidden></section>
<section id="t-bookings" hidden></section>
@endsection
