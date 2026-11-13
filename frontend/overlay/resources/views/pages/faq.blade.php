@extends('layouts.app')
@section('title', 'FAQ')
@section('page', 'faq')
@section('content')
<div class="card narrow" style="max-width:760px;margin:32px auto">
  <h1 class="h1">Questions fréquentes</h1>
  <h2 class="h2" style="font-size:20px">Comment réserver un événement ?</h2>
  <p>Créez un compte participant, choisissez un événement dans le catalogue, indiquez le nombre de places et validez. Le paiement est simulé en démonstration : vous pouvez tester un paiement réussi ou échoué.</p>
  <h2 class="h2" style="font-size:20px">Où trouver mon billet ?</h2>
  <p>Après confirmation de votre réservation, rendez-vous dans « Mon espace » puis l'onglet « Mes billets ». Chaque billet possède une référence unique et un QR code, téléchargeable en PDF.</p>
  <h2 class="h2" style="font-size:20px">Puis-je annuler ma réservation ?</h2>
  <p>Oui, depuis « Mes réservations », tant que le statut est « En attente » ou « Confirmée ». Les places sont alors libérées.</p>
  <h2 class="h2" style="font-size:20px">Comment publier un événement ?</h2>
  <p>Inscrivez-vous en tant qu'organisateur, créez votre événement dans votre espace, puis soumettez-le à validation. Il devient visible dans le catalogue une fois approuvé par l'administration.</p>
</div>
@endsection
