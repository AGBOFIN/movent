@extends('layouts.app')
@section('title', 'Lieux')
@section('page', 'venues')
@section('content')
<h1 class="h1">Lieux d'événements</h1>
<p class="muted">Hôtels, palais, stades et centres culturels où se tiennent conférences, concerts et événements à Lomé, Kara et ailleurs au Togo.</p>
<div class="filters" style="grid-template-columns:2fr 1fr">
  <input id="vq" type="search" placeholder="Rechercher un lieu, une ville, une adresse…">
  <select id="vcity"></select>
</div>
<div id="vgrid" class="grid"></div>
@endsection
