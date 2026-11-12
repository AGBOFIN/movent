@extends('layouts.app')
@section('title', 'Inscription')
@section('page', 'register')
@section('content')
<form id="form" class="card narrow">
  <h1 class="h1">Créer un compte</h1>
  <div class="row2"><label>Prénom<input name="first_name" required></label><label>Nom<input name="last_name" required></label></div>
  <label>E-mail<input name="email" type="email" required autocomplete="email"></label>
  <label>Téléphone (facultatif)<input name="phone" type="tel"></label>
  <label>Mot de passe (8 caractères minimum)<input name="password" type="password" minlength="8" required autocomplete="new-password"></label>
  <label>Je suis
    <select name="role"><option value="PARTICIPANT">Participant (je réserve)</option><option value="ORGANIZER">Organisateur (je publie des événements)</option></select>
  </label>
  <button class="btn block">Créer mon compte</button>
  <p class="muted center">Déjà inscrit ? <a href="/login">Se connecter</a></p>
</form>
@endsection
