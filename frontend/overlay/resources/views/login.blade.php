@extends('layouts.app')
@section('title', 'Connexion')
@section('page', 'login')
@section('content')
<form id="form" class="card narrow">
  <h1 class="h1">Connexion</h1>
  <label>E-mail<input name="email" type="email" required autocomplete="email"></label>
  <label>Mot de passe<input name="password" type="password" required autocomplete="current-password"></label>
  <button class="btn block">Se connecter</button>
  <p class="muted center">Pas encore de compte ? <a href="/register">S’inscrire</a></p>
  <p class="muted small">Démo : participant1@movent.test, organizer1@movent.test ou admin@movent.test — mot de passe Password123!</p>
</form>
@endsection
