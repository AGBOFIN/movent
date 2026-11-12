@extends('layouts.app')
@section('title', 'Mon espace')
@section('page', 'account')
@section('content')
<h1 class="h1">Mon espace participant</h1>
<div class="tabs" id="tabs">
  <button data-t="bookings" class="on">Mes réservations</button><button data-t="tickets">Mes billets</button><button data-t="profile">Profil</button>
</div>
<section id="t-bookings"></section>
<section id="t-tickets" hidden></section>
<section id="t-profile" hidden></section>
@endsection
