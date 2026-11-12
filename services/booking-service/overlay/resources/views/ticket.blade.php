<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
  /* MOVENT ticket — A4, noir et blanc (dompdf-safe : pas de SVG externe) */
  @page { margin: 18mm; }
  * { box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; color: #000; margin: 0; font-size: 13px; }
  .ticket { border: 2px solid #000; border-radius: 14px; overflow: hidden; }
  .head { display: table; width: 100%; border-bottom: 2px dashed #000; }
  .brand { display: table-cell; vertical-align: middle; padding: 16px 24px; }
  .ticket-logo { display: inline-block; width: 46px; height: 30px; border: 2px solid #000; border-radius: 8px; position: relative; vertical-align: middle; }
  .ticket-logo::before, .ticket-logo::after { content: ""; position: absolute; top: 50%; width: 12px; height: 12px; border-radius: 50%; background: #fff; border: 2px solid #000; transform: translateY(-50%); }
  .ticket-logo::before { left: -8px; } .ticket-logo::after { right: -8px; }
  .brand b { font-size: 22px; letter-spacing: 4px; margin-left: 10px; vertical-align: middle; }
  .brand span { display: block; font-size: 10px; letter-spacing: 1px; color: #444; margin-left: 56px; }
  .stub { display: table-cell; width: 34%; vertical-align: middle; text-align: center; padding: 18px 10px; border-left: 2px dashed #000; }
  .stub .lab { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #444; }
  .stub .ref { font-family: DejaVu Sans Mono, monospace; font-size: 15px; font-weight: bold; letter-spacing: 1px; margin-top: 4px; }
  .stub img { width: 150px; height: 150px; margin-top: 8px; }
  .stub .hint { font-size: 10px; color: #444; margin-top: 6px; }
  .main { padding: 22px 26px; }
  h1 { font-size: 24px; margin: 0 0 4px; }
  .loc { color: #444; font-size: 12px; margin-bottom: 18px; }
  table.info { width: 100%; border-collapse: collapse; }
  td { vertical-align: top; padding: 7px 0; font-size: 13px; width: 50%; }
  .lab { color: #444; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 2px; }
  .foot { border-top: 2px dashed #000; padding: 12px 24px; font-size: 10px; color: #444; display: flex; justify-content: space-between; }
</style>
</head>
<body>
<div class="ticket">
  <div class="head">
    <div class="brand"><span class="ticket-logo"></span><b>MOVENT</b><span>Discover. Book. Experience.</span></div>
    <div class="stub">
      <div class="lab">Référence billet</div>
      <div class="ref">{{ $ticket->ticket_reference }}</div>
      <img src="{{ $qr }}" width="150" height="150" alt="QR code">
      <div class="hint">Présentez ce QR code à l'entrée</div>
    </div>
  </div>
  <div class="main">
    <h1>{{ $booking->event_title }}</h1>
    <div class="loc">{{ $booking->event_location }}</div>
    <table class="info">
      <tr>
        <td><span class="lab">Date</span>{{ optional($booking->event_start_at)->format('d/m/Y à H:i') }}</td>
        <td><span class="lab">Participant</span>{{ $booking->participant_name }}</td>
      </tr>
      <tr>
        <td><span class="lab">Réservation</span>{{ $booking->reference }}</td>
        <td><span class="lab">Statut</span>{{ $ticket->status }}</td>
      </tr>
    </table>
  </div>
  <div class="foot">
    <span>MOVENT — Discover. Book. Experience.</span>
    <span>Billet strictement personnel, valable une seule fois.</span>
  </div>
</div>
</body>
</html>
