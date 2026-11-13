@extends('layouts.app')
@section('title', 'Politique de confidentialité')
@section('page', 'privacy')
@section('content')
<div class="card narrow" style="max-width:760px;margin:32px auto">
  <h1 class="h1">Politique de confidentialité</h1>
  <h2 class="h2" style="font-size:20px">Données collectées</h2>
  <p>Nous collectons les informations nécessaires au fonctionnement du service : nom, prénom, e-mail, téléphone, réservations et billets. Aucune donnée n'est vendue à des tiers.</p>
  <h2 class="h2" style="font-size:20px">Utilisation</h2>
  <p>Ces données servent à gérer les comptes, les réservations, l'émission des billets et des finalités de supervision et d'amélioration du service.</p>
  <h2 class="h2" style="font-size:20px">Sécurité</h2>
  <p>Les mots de passe sont hachés, les échanges sont chiffrés et les accès sont contrôlés par rôle. Les jetons de session expirent automatiquement.</p>
  <h2 class="h2" style="font-size:20px">Vos droits</h2>
  <p>Vous pouvez consulter et modifier vos informations depuis « Mon espace ». Pour toute demande de suppression, contactez <a href="mailto:contact@movent.tg">contact@movent.tg</a>.</p>
</div>
@endsection
