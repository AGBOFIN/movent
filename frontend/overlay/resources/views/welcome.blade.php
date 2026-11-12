<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>MOVENT — Discover. Book. Experience.</title>
<meta name="description" content="MOVENT — la plateforme togolaise de découverte et de réservation d'événements : concerts, conférences, festivals, salons.">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<meta property="og:title" content="MOVENT — Discover. Book. Experience.">
<meta property="og:description" content="Découvrez, réservez et vivez les événements publics et privés au Togo.">
<meta property="og:image" content="{{ asset('img/og-image.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>
<nav class="nav">
  <a class="logo" href="/"><img src="{{ asset('img/logo.svg') }}" alt="MOVENT"></a>
  <div class="navlinks">
    <a href="#catalogue">Événements</a>
    <a href="{{ url('/lieux') }}" class="hide-sm">Lieux</a>
    <a href="{{ url('/faq') }}" class="hide-sm">FAQ</a>
    <a href="{{ url('/login') }}">Connexion</a>
    <a class="pill" href="{{ url('/register') }}">Inscription</a>
  </div>
</nav>

<section class="hero">
  <h1>Vos événements.<br><em>Vos gens.</em> Une plateforme.</h1>
  <p class="sub">Découvrez, réservez et vivez les événements publics et privés partout au Togo — concerts, festivals, conférences, cérémonies.</p>
  <form class="searchbar" id="searchbar" role="search">
    <input id="q" type="search" placeholder="Rechercher un événement, un lieu…" aria-label="Rechercher un événement">
    <button class="pill" type="submit">Rechercher</button>
  </form>
  <div class="filters" id="filters">
    <div id="chips" class="filters" style="margin:0;justify-content:center"></div>
    <select class="fsel" id="kind" aria-label="Type d'organisateur">
      <option value="">Tous les organisateurs</option>
      <option value="INSTITUTION">Institutions</option>
      <option value="PRIVATE">Privés</option>
    </select>
    <label class="chk" style="color:var(--ink2);font-size:13.5px;display:flex;align-items:center;gap:8px"><input type="checkbox" id="free" style="accent-color:var(--accent)"> Gratuits</label>
  </div>
</section>

<section class="section" id="catalogue">
  <div class="section-head">
    <h2>Événements à la une</h2>
    <a href="{{ url('/events') }}">Voir tout le catalogue →</a>
  </div>
  <div class="grid" id="grid"></div>
  <p class="center" style="margin-top:28px"><button class="pill ghost" id="more" hidden>Charger plus</button></p>
</section>

<section class="section">
  <div class="section-head">
    <h2>Lieux populaires</h2>
    <a href="{{ url('/lieux') }}">Tous les lieux →</a>
  </div>
  <div class="grid" id="venues"></div>
</section>

<section class="section">
  <div class="section-head"><h2>Prochains événements par ville</h2></div>
  <div class="cityrow" id="cityrow"></div>
</section>

<section class="section">
  <div class="section-head"><h2>Comment ça marche</h2></div>
  <div class="steps">
    <div class="step"><b>1. Découvrez</b><p>Parcourez le catalogue, filtrez par catégorie, lieu ou ville, et trouvez l'événement qui vous ressemble.</p></div>
    <div class="step"><b>2. Réservez</b><p>Choisissez vos places, réglez en ligne et recevez immédiatement votre billet avec QR code unique.</p></div>
    <div class="step"><b>3. Vivez</b><p>Présentez votre billet à l'entrée et profitez de l'événement en toute sérénité.</p></div>
  </div>
</section>

<section class="section">
  <div class="reassure">
    <div><b>Paiement sécurisé</b><p>Réglez vos billets en toute confiance, avec confirmation immédiate.</p></div>
    <div><b>Billet QR unique</b><p>Un code unique par billet, vérifiable à l'entrée, disponible en PDF.</p></div>
    <div><b>Assistance</b><p>Une équipe disponible pour vous accompagner avant, pendant et après l'événement.</p></div>
  </div>
</section>

<footer class="foot">
  <div class="foot-in">
    <div>
      <a class="logo" href="/"><img src="{{ asset('img/logo.svg') }}" alt="MOVENT" style="height:36px"></a>
      <p class="muted" style="color:var(--ink2);font-size:14px;margin:14px 0 0;max-width:340px">Discover. Book. Experience. — La plateforme togolaise de découverte et de réservation d'événements.</p>
    </div>
    <div>
      <h3>Explorer</h3>
      <ul>
        <li><a href="{{ url('/events') }}">Événements</a></li>
        <li><a href="{{ url('/lieux') }}">Lieux</a></li>
        <li><a href="{{ url('/a-propos') }}">À propos</a></li>
      </ul>
    </div>
    <div>
      <h3>Compte</h3>
      <ul>
        <li><a href="{{ url('/login') }}">Connexion</a></li>
        <li><a href="{{ url('/register') }}">Inscription</a></li>
        <li><a href="{{ url('/faq') }}">FAQ</a></li>
        <li><a href="{{ url('/contact') }}">Contact</a></li>
      </ul>
    </div>
    <div>
      <h3>Légal</h3>
      <ul>
        <li><a href="{{ url('/mentions-legales') }}">Mentions légales</a></li>
        <li><a href="{{ url('/conditions') }}">Conditions d'utilisation</a></li>
        <li><a href="{{ url('/confidentialite') }}">Politique de confidentialité</a></li>
      </ul>
    </div>
  </div>
  <div class="legal"><span>© 2026 MOVENT. Tous droits réservés.</span><span>Discover. Book. Experience.</span></div>
</footer>

<script src="{{ asset('js/landing.js') }}" defer></script>
</body>
</html>
