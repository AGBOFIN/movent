<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>MOVENT — Discover. Book. Experience.</title>
<meta name="description" content="MOVENT — la plateforme togolaise de découverte et de réservation d'événements : concerts, conférences, festivals, salons, cérémonies publiques et privées.">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<meta property="og:title" content="MOVENT — Discover. Book. Experience.">
<meta property="og:description" content="Découvrez, réservez et vivez les événements publics et privés au Togo.">
<meta property="og:image" content="{{ asset('img/og-image.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>

<nav class="nav">
  <a class="logo" href="/"><img src="{{ asset('img/logo-light.svg') }}" alt="MOVENT"></a>
  <div class="navlinks">
    <a href="#catalogue">Événements</a>
    <a href="{{ url('/lieux') }}" class="hide-sm">Lieux</a>
    <a href="#pourquoi" class="hide-sm">Pourquoi MOVENT</a>
    <a href="{{ url('/faq') }}" class="hide-sm">FAQ</a>
    <a href="{{ url('/login') }}">Connexion</a>
    <a class="pill" href="{{ url('/register') }}">Inscription</a>
  </div>
</nav>

<!-- ================= HERO ================= -->
<section class="hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="hero-in">
    <span class="eyebrow">Plateforme nationale — Togo</span>
    <h1>Vos événements.<br><em>Vos gens.</em> Une plateforme.</h1>
    <p class="sub">MOVENT centralise la découverte et la réservation d'événements publics et privés sur tout le territoire togolais — concerts, festivals, conférences, salons, cérémonies. Réservez en quelques clics, recevez votre billet QR unique.</p>

    <form class="searchbar" id="searchbar" role="search">
      <svg class="s-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
      <input id="q" type="search" placeholder="Rechercher un événement, un lieu, une ville…" aria-label="Rechercher un événement">
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

    <div class="herostats" id="herostats">
      <div><b data-count="events">—</b><span>événements à venir</span></div>
      <div><b data-count="venues">—</b><span>lieux partenaires</span></div>
      <div><b data-count="cities">—</b><span>villes couvertes</span></div>
      <div><b>QR</b><span>billets infalsifiables</span></div>
    </div>
  </div>
</section>

<!-- ================= STATS ================= -->
<section class="strip" aria-label="Chiffres clés">
  <div><b data-count="events">—</b><span>Événements publiés</span></div>
  <div><b data-count="venues">—</b><span>Lieux référencés</span></div>
  <div><b data-count="cities">—</b><span>Villes couvertes</span></div>
  <div><b>4</b><span>Espaces dédiés</span></div>
</section>

<!-- ================= POURQUOI ================= -->
<section class="section" id="pourquoi">
  <div class="section-head reveal">
    <div>
      <span class="eyebrow">Pourquoi MOVENT</span>
      <h2>Une infrastructure pour tous les événements du Togo</h2>
    </div>
    <p class="lede">Publics ou privés, gratuits ou payants : chaque événement trouve sa place, chaque participant son billet.</p>
  </div>
  <div class="bento">
    <article class="bento-card wide reveal">
      <div class="b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/><path d="M9 21v-6h6v6"/></svg></div>
      <h3>Événements publics &amp; privés</h3>
      <p>Institutions, ministères, collectivités et organisateurs privés partagent la même plateforme : un catalogue unique, des règles de validation identiques pour tous.</p>
    </article>
    <article class="bento-card reveal">
      <div class="b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M8 12h8M12 8v8"/></svg></div>
      <h3>Billets QR uniques</h3>
      <p>Chaque billet est généré après confirmation du paiement, avec un QR code unique et un PDF téléchargeable.</p>
    </article>
    <article class="bento-card reveal">
      <div class="b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="3"/><path d="M3 10h18"/><path d="M7 15h4"/></svg></div>
      <h3>Paiement sécurisé</h3>
      <p>Réservez et réglez en ligne. Les places sont bloquées pendant le paiement — impossible de revendre une place déjà prise.</p>
    </article>
    <article class="bento-card wide reveal">
      <div class="b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6z"/><path d="m9 12 2 2 4-4"/></svg></div>
      <h3>Modération et confiance</h3>
      <p>Chaque événement est examiné avant publication. Les organisateurs sont identifiés, les places comptabilisées en temps réel et les transactions sécurisées.</p>
    </article>
    <article class="bento-card reveal">
      <div class="b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div>
      <h3>Réservation en direct</h3>
      <p>Places disponibles en temps réel, annulation simple, places automatiquement libérées en cas d'échec de paiement.</p>
    </article>
    <article class="bento-card reveal">
      <div class="b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></div>
      <h3>Couverture nationale</h3>
      <p>De Lomé à Kara, en passant par les plages et les sites culturels : les lieux partenaires sont référencés ville par ville.</p>
    </article>
  </div>
</section>

<!-- ================= CATALOGUE ================= -->
<section class="section" id="catalogue">
  <div class="section-head reveal">
    <div>
      <span class="eyebrow">Catalogue</span>
      <h2>Événements à la une</h2>
    </div>
    <a class="link-more" href="{{ url('/events') }}">Voir tout le catalogue →</a>
  </div>
  <div class="grid" id="grid"></div>
  <p class="center" style="margin-top:28px"><button class="pill ghost" id="more" hidden>Charger plus d'événements</button></p>
</section>

<!-- ================= LIEUX ================= -->
<section class="section">
  <div class="section-head reveal">
    <div>
      <span class="eyebrow">Lieux</span>
      <h2>Lieux partenaires</h2>
    </div>
    <a class="link-more" href="{{ url('/lieux') }}">Tous les lieux →</a>
  </div>
  <div class="grid" id="venues"></div>
</section>

<!-- ================= RÔLES ================= -->
<section class="section">
  <div class="section-head reveal">
    <div>
      <span class="eyebrow">Espaces</span>
      <h2>Deux espaces, un même écosystème</h2>
    </div>
    <p class="lede">Chaque profil dispose de son espace dédié, avec des permissions adaptées.</p>
  </div>
  <div class="roles">
    <a class="role reveal" href="{{ url('/register') }}">
      <span class="role-tag">Public</span>
      <h3>Participant</h3>
      <p>Découvrez le catalogue, réservez vos places, payez en ligne et retrouvez vos billets QR dans votre espace personnel.</p>
      <span class="role-go">Créer un compte →</span>
    </a>
    <a class="role reveal" href="{{ url('/register') }}">
      <span class="role-tag">Organisateur</span>
      <h3>Organisateur</h3>
      <p>Créez vos événements, choisissez un lieu partenaire, soumettez à validation et suivez vos réservations en temps réel.</p>
      <span class="role-go">Devenir organisateur →</span>
    </a>
  </div>
</section>

<!-- ================= ÉTAPES ================= -->
<section class="section">
  <div class="section-head reveal">
    <div>
      <span class="eyebrow">Parcours</span>
      <h2>Comment ça marche</h2>
    </div>
  </div>
  <div class="steps">
    <div class="step reveal"><span class="step-n">01</span><b>Découvrez</b><p>Parcourez le catalogue, filtrez par catégorie, type d'organisateur, ville ou lieu, et trouvez l'événement qui vous ressemble.</p></div>
    <div class="step reveal"><span class="step-n">02</span><b>Réservez</b><p>Choisissez vos places, réglez en ligne. Vos places sont bloquées le temps du paiement — personne d'autre ne peut les prendre.</p></div>
    <div class="step reveal"><span class="step-n">03</span><b>Vivez</b><p>Recevez immédiatement votre billet avec QR code unique, téléchargez le PDF et présentez-le à l'entrée.</p></div>
  </div>
</section>

<!-- ================= VILLES ================= -->
<section class="section">
  <div class="section-head reveal">
    <div>
      <span class="eyebrow">Territoire</span>
      <h2>Prochains événements par ville</h2>
    </div>
  </div>
  <div class="cityrow" id="cityrow"></div>
</section>

<!-- ================= RÉASSURANCE ================= -->
<section class="section">
  <div class="reassure">
    <div class="reveal"><b>Paiement sécurisé</b><p>Réglez vos billets en toute confiance, avec confirmation immédiate et libération automatique des places en cas d'échec.</p></div>
    <div class="reveal"><b>Billet QR unique</b><p>Un code unique par billet, vérifiable à l'entrée, disponible en PDF à tout moment.</p></div>
    <div class="reveal"><b>Assistance</b><p>Une équipe disponible pour vous accompagner avant, pendant et après l'événement.</p></div>
  </div>
</section>

<!-- ================= CTA ================= -->
<section class="cta">
  <div class="cta-in reveal">
    <h2>Prêt à vivre vos événements ?</h2>
    <p>Rejoignez la plateforme nationale de découverte et de réservation d'événements au Togo.</p>
    <div class="cta-btns">
      <a class="pill big" href="{{ url('/register') }}">Créer un compte gratuit</a>
      <a class="pill ghost big" href="{{ url('/events') }}">Explorer le catalogue</a>
    </div>
  </div>
</section>

<footer class="foot">
  <div class="foot-in">
    <div>
      <a class="logo" href="/"><img src="{{ asset('img/logo-light.svg') }}" alt="MOVENT" style="height:36px"></a>
      <p class="muted" style="color:var(--ink2);font-size:14px;margin:14px 0 0;max-width:340px">Discover. Book. Experience. — La plateforme togolaise de découverte et de réservation d'événements publics et privés.</p>
    </div>
    <div>
      <h3>Explorer</h3>
      <ul>
        <li><a href="{{ url('/events') }}">Événements</a></li>
        <li><a href="{{ url('/lieux') }}">Lieux</a></li>
        <li><a href="{{ url('/a-propos') }}">À propos</a></li>
        <li><a href="{{ url('/faq') }}">FAQ</a></li>
      </ul>
    </div>
    <div>
      <h3>Compte</h3>
      <ul>
        <li><a href="{{ url('/login') }}">Connexion</a></li>
        <li><a href="{{ url('/register') }}">Inscription</a></li>
        <li><a href="{{ url('/contact') }}">Contact</a></li>
        <li><a href="{{ url('/account') }}">Mon espace</a></li>
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
