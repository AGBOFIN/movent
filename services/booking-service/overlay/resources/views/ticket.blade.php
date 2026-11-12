<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; color: #10172a; margin: 0; }
  .wrap { border: 2px solid #10172a; border-radius: 14px; padding: 0; }
  .head { background: #10172a; color: #d6ae69; padding: 18px 24px; font-size: 26px; letter-spacing: 3px; }
  .body { padding: 24px; }
  h1 { font-size: 24px; margin: 0 0 6px; }
  .muted { color: #64748b; font-size: 12px; }
  table { width: 100%; border-collapse: collapse; margin-top: 18px; }
  td { vertical-align: top; padding: 6px 0; font-size: 13px; }
  .lab { color: #64748b; font-size: 11px; text-transform: uppercase; }
  .qr { text-align: center; }
  .ref { font-size: 15px; font-weight: bold; letter-spacing: 1px; }
  .foot { border-top: 1px dashed #64748b; padding: 12px 24px; font-size: 11px; color: #64748b; }
</style>
</head>
<body>
<div class="wrap">
  <div class="head">MOVENT</div>
  <div class="body">
    <h1>{{ $booking->event_title }}</h1>
    <div class="muted">Billet d'entrée — {{ $booking->event_location }}</div>
    <table>
      <tr>
        <td>
          <div class="lab">Date</div>{{ optional($booking->event_start_at)->format('d/m/Y H:i') }}<br><br>
          <div class="lab">Participant</div>{{ $booking->participant_name }}<br><br>
          <div class="lab">Référence billet</div><span class="ref">{{ $ticket->ticket_reference }}</span><br><br>
          <div class="lab">Réservation</div>{{ $booking->reference }}
        </td>
        <td class="qr"><img src="{{ $qr }}" width="170" height="170"><div class="muted">Présentez ce QR code à l'entrée</div></td>
      </tr>
    </table>
  </div>
  <div class="foot">MOVENT — Discover. Book. Experience. Billet strictement personnel, valable une seule fois.</div>
</div>
</body>
</html>
