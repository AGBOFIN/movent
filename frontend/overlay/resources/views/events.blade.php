@extends('layouts.app')
@section('title', 'Catalogue')
@section('page', 'events')
@section('content')
<h1 class="h1">Catalogue des événements</h1>
<div class="filters">
  <input id="q" type="search" placeholder="Rechercher un événement ou un lieu…">
  <select id="cat"></select>
  <select id="kind"><option value="">Tous les organisateurs</option><option value="INSTITUTION">Institutions</option><option value="PRIVATE">Organisateurs privés</option></select>
  <label class="chk"><input id="free" type="checkbox"> Gratuits uniquement</label>
</div>
<div id="grid" class="grid"></div>
<p class="center"><button id="more" class="btn ghost" hidden>Voir plus</button></p>
@endsection
