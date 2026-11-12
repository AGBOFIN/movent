@extends('layouts.app')
@section('title', 'Administration')
@section('page', 'admin')
@section('content')
<h1 class="h1">Administration</h1>
<div id="stats" class="stats"></div>
<div class="tabs" id="tabs">
  <button data-t="moderation" class="on">Modération</button><button data-t="events">Événements</button><button data-t="users">Utilisateurs</button><button data-t="bookings">Réservations</button>
</div>
<section id="t-moderation"></section>
<section id="t-events" hidden></section>
<section id="t-users" hidden></section>
<section id="t-bookings" hidden></section>
@endsection
