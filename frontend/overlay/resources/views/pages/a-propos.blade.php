@extends('layouts.app')
@section('title', 'À propos')
@section('page', 'about')
@section('content')
<div class="card narrow" style="max-width:760px;margin:32px auto">
  <h1 class="h1">À propos de MOVENT</h1>
  <p>MOVENT est une plateforme togolaise de découverte et de réservation d'événements. Elle permet aux participants de trouver facilement concerts, conférences, festivals, salons et spectacles — et aux organisateurs de publier et gérer leurs événements en toute simplicité.</p>
  <h2 class="h2">Discover. Book. Experience.</h2>
  <ul>
    <li><b>Discover</b> — parcourez le catalogue des événements approuvés, filtrés par catégorie, lieu et ville.</li>
    <li><b>Book</b> — réservez vos places en quelques clics, avec paiement sécurisé et billets nominatifs.</li>
    <li><b>Experience</b> — présentez votre billet avec son QR code unique à l'entrée de l'événement.</li>
  </ul>
  <p class="muted">Chaque événement publié passe par une modération avant d'être visible dans le catalogue. Les billets sont générés uniquement après confirmation de la réservation.</p>
</div>
@endsection
