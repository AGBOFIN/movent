<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>MOVENT — Discover. Book. Experience.</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>
<svg width="0" height="0" style="position:absolute" aria-hidden="true"><symbol id="i-person" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" fill="currentColor"/><path d="M4.5 20c.8-3.6 3.9-5.6 7.5-5.6s6.7 2 7.5 5.6z" fill="currentColor"/></symbol></svg>
<nav class="nav"><div class="logo">MOVENT</div><span><a class="pill" style="background:transparent;color:#f5ecd9;border:1px solid #f5ecd955;margin-right:8px" href="{{ url('/events') }}">Catalogue</a><a class="pill" href="{{ url('/register') }}">Rejoindre</a></span></nav>

<section class="hero">
  <h1><span>Vos Événements.</span><b>Vos Gens. Une Plateforme</b></h1>
  <div class="stage">
    <div class="ring"></div>
    <div class="scene">
      <div class="orbit"><div class="ev" style="--i:0;background:radial-gradient(circle at 50% 0,#ffe3a8 0,transparent 45%),linear-gradient(180deg,#4a3a96,#1c1236 75%)"><div class="cap"><b>Nuit Live</b>Sam. 21 nov.</div></div><div class="ev" style="--i:1;background:linear-gradient(180deg,#f4a259 0,#d9534f 55%,#3a1f3d 100%)"><div class="cap"><b>Festival Soleil</b>Dim. 22 nov.</div></div><div class="ev" style="--i:2;background:radial-gradient(circle at 50% 10%,#9cc4ee 0,transparent 50%),linear-gradient(180deg,#4f7fc0,#1f3a64)"><div class="cap"><b>Tech Summit</b>Mar. 24 nov.</div></div><div class="ev" style="--i:3;background:radial-gradient(circle at 50% 30%,#f3d9a0,#b4833a 60%,#3a2a12)"><div class="cap"><b>Gala d’Or</b>Ven. 27 nov.</div></div><div class="ev" style="--i:4;background:linear-gradient(180deg,#8fc7e8 0,#6fb38a 50%,#245a3a 100%)"><div class="cap"><b>Finale du Stade</b>Sam. 28 nov.</div></div><div class="ev" style="--i:5;background:radial-gradient(circle at 50% 0,#ffd9a8 0,transparent 40%),repeating-linear-gradient(90deg,#8e2a35 0 14px,#6f1f2a 14px 28px)"><div class="cap"><b>Soir de Rideau</b>Dim. 29 nov.</div></div></div>
      <div class="orbit ppl"><div class="person" style="--a:0deg;background:#e2703a"><svg><use href="#i-person"/></svg></div><div class="person" style="--a:51.4deg;background:#d9a441"><svg><use href="#i-person"/></svg></div><div class="person" style="--a:102.9deg;background:#9ab584"><svg><use href="#i-person"/></svg></div><div class="person" style="--a:154.3deg;background:#c96f4a"><svg><use href="#i-person"/></svg></div><div class="person" style="--a:205.7deg;background:#7a8c5f"><svg><use href="#i-person"/></svg></div><div class="person" style="--a:257.1deg;background:#b4552d"><svg><use href="#i-person"/></svg></div><div class="person" style="--a:308.6deg;background:#8a5a44"><svg><use href="#i-person"/></svg></div></div>
      <div class="ph"><h4>Découvrir</h4>
        <div class="tabs"><span>Tout</span><span>Concerts</span><span>Soirées</span></div>
        <div style="font-size:11px;margin:8px 0">Catégories populaires</div>
        <div class="gen"><div style="background:#c9d6b4">Concert</div><div style="background:#b7a58c">Conférence</div><div style="background:#d9a441">Festival</div><div style="background:#c96f4a">Sport</div></div>
        <div style="font-size:11px;margin:12px 0 6px">Pour vous</div>
        <div style="height:70px;border-radius:14px;background:linear-gradient(120deg,#b4552d,#7a8c5f)"></div>
      </div>
    </div>
    <figure class="crowd"><img src="{{ asset('img/hero-crowd.jpg') }}" alt="Public en liesse lors d'un festival de musique en Afrique"><figcaption>Nativeland — Lagos<span>CC BY-SA</span></figcaption></figure>
  </div>
</section>

<section class="future" id="a-propos">
  <p>Imaginez un monde où les organisateurs trouvent leur public, où les participants découvrent des événements rares et où réserver devient simple et accessible pour tous.</p>
  <div style="overflow:hidden"><div class="track" id="trk"></div></div>
  <h2>L’avenir de la<br>découverte d’événements</h2>
  <div class="chip">Ensemble, nous façonnons l’avenir des événements : la création prospère, chaque rencontre compte.</div>
</section>

<section class="shatter">
  <h2>Briser les</h2><h2 class="g">limites des</h2><h2 class="g">réservations</h2><h2>traditionnelles</h2>
</section>

<section class="split" id="participants">
  <div class="half l"><h2>Pour les participants</h2><p>Trouver un événement, réserver ses places et recevoir son billet QR : tout se fait en quelques clics, sans friction.</p><div class="big">Fans</div><div class="vinyl"></div></div>
  <div class="half a"><h2>Pour les organisateurs</h2><p>Créer, soumettre à validation, suivre les réservations et les statistiques depuis un seul tableau de bord.</p><div class="big">Pros</div><div class="vinyl"></div></div>
</section>

<section class="connect">
  <h2>Découvrir.<br>Réserver.<br>Vivre.</h2>
  <div class="right"><div class="acc" id="acc1"></div></div>
</section>

<section class="unite">
  <div class="fc" style="left:8%;top:18%;background:#b4552d">🎤</div>
  <div class="fc" style="left:18%;bottom:10%;background:#d9a441">🎸</div>
  <div class="fc" style="right:10%;top:14%;background:#7a8c5f">🎟️</div>
  <div class="fc" style="right:16%;bottom:12%;background:#8a5a44">🎧</div>
  <div><h2><span>Unissez</span>l’ambiance</h2>
  <p class="y">Des rencontres vraies, des moments partagés et des expériences qui comptent pour les organisateurs comme pour le public.</p>
  <p class="g">Chaque événement validé par notre équipe, chaque billet unique, chaque place réellement disponible.</p></div>
</section>

<section class="nj">
  <h2><em>Pas seulement</em> réserver</h2>
  <div class="acc" id="acc2"></div>
</section>

<section class="mus">
  <small>Toutes les catégories, un seul endroit</small>
  <h2>EVENTS</h2>
  <div class="tags"><span>Concert</span><span>Festival</span><span>Conférence</span><span>Soirée</span><span>Sport</span><span>Théâtre</span><span>Exposition</span><span>Atelier</span><span>Mariage</span><span>Gala</span></div>
</section>

<section class="ex" id="organisateurs">
  <div class="p"><h2>L’excellence pour les organisateurs</h2><div class="tk"><b>Mon événement</b><div style="font-size:11px;color:#bbb">En attente → Validé</div><div style="margin-top:14px;height:12px;border-radius:9px;background:var(--lime);width:70%"></div></div></div>
  <div class="mid"><a class="pill" style="background:#fff;border:6px solid #000;box-shadow:0 0 0 6px #000" href="{{ url('/register') }}">Rejoindre</a></div>
  <div class="l"><h2>L’accessibilité pour les participants</h2><div class="tk" style="transform:translateX(-50%) rotate(10deg)"><b>Votre billet</b><div class="qr"></div></div></div>
</section>

<section class="foot" id="footer">
  <div class="logo" style="justify-content:center;font-size:44px;margin-bottom:20px">MOVENT</div>
  <h2>Pour ceux qui<br>vivent les événements</h2>
  <p style="margin:24px 0"><a class="pill" href="{{ url('/register') }}">Créer mon compte</a> <a class="pill" style="background:#fff" href="{{ url('/events') }}">Voir les événements</a></p>
  <div class="flinks">
    <a class="flink" href="#a-propos"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><circle cx="12" cy="8" r=".5" fill="currentColor"/></svg>À propos</a>
    <a class="flink" href="#organisateurs"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/></svg>Organisateurs</a>
    <a class="flink" href="#participants"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M3 20c.6-3.4 3-5.5 6-5.5s5.4 2.1 6 5.5"/><circle cx="17" cy="9" r="2.6"/><path d="M16 14.8c2.6.3 4.4 2 5 4.7"/></svg>Participants</a>
    <a class="flink" href="#footer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7 8.5 6 8.5-6"/></svg>Contact</a>
  </div>
  <div class="legal"><span>© 2026 MOVENT. Tous droits réservés.</span><span>Photo : O. Sutherland / Wikimedia Commons (CC BY-SA 4.0)</span><span>Discover. Book. Experience.</span></div>
</section>

<script src="{{ asset('js/landing.js') }}" defer></script>
</body>
</html>
