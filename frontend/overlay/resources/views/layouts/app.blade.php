<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'MOVENT') — MOVENT</title>
<meta name="description" content="MOVENT — Discover. Book. Experience. Plateforme de découverte et de réservation d'événements.">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<meta property="og:title" content="MOVENT — Discover. Book. Experience.">
<meta property="og:description" content="Découvrez, réservez et vivez les événements publics et privés.">
<meta property="og:image" content="{{ asset('img/og-image.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body data-page="@yield('page')" data-id="@yield('id')">
<header id="nav" class="top"></header>
<main class="wrap">@yield('content')</main>
<footer class="foot">
  <span class="foot-links"><a href="/events">Événements</a><a href="/lieux">Lieux</a><a href="/contact">Contact</a><a href="/conditions">Conditions</a><a href="/confidentialite">Confidentialité</a></span>
  <img src="{{ asset('img/logo-icon.svg') }}" alt="">
  <span>© 2026 MOVENT — Discover. Book. Experience.</span>
</footer>
<div id="toast" class="toast" hidden></div>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
