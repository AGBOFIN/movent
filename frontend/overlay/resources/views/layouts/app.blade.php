<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'MOVENT') — MOVENT</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Plus+Jakarta+Sans:wght@400;500;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body data-page="@yield('page')" data-id="@yield('id')">
<header id="nav" class="top"></header>
<main class="wrap">@yield('content')</main>
<footer class="foot">© 2026 MOVENT — Discover. Book. Experience.</footer>
<div id="toast" class="toast" hidden></div>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
