@extends('layouts.app')
@section('title', 'Paiement réussi')
@section('page', 'payment-success')
@section('content')
<div class="card center" style="max-width:640px;margin:0 auto;display:grid;gap:18px;justify-items:center;text-align:center;padding:44px 32px">
  <div style="width:76px;height:76px;border-radius:50%;background:var(--ok-bg);border:1px solid var(--ok-bd);color:var(--ok-text);display:grid;place-items:center">
    <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m4.5 12.5 5 5 10-11"/></svg>
  </div>
  <h1 class="h1" style="margin:0">Paiement réussi !</h1>
  <p class="muted" style="margin:0">Votre réservation est confirmée. Vos billets avec QR code unique sont disponibles dans votre espace personnel — vous pouvez les télécharger en PDF à tout moment.</p>
  <div class="between" style="gap:12px;flex-wrap:wrap;justify-content:center">
    <a class="btn" href="/account#tickets">Voir mes billets</a>
    <a class="btn ghost" href="/events">Continuer à explorer</a>
  </div>
  <p class="small muted" style="margin:0">Réservation <span class="mono" id="ref"></span></p>
</div>
<script>
document.getElementById('ref').textContent = new URLSearchParams(location.search).get('ref') || '';
</script>
@endsection
