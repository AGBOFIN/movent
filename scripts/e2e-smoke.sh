#!/usr/bin/env bash
# MOVENT — parcours de validation de bout en bout (API via la passerelle).
# Usage : bash scripts/e2e-smoke.sh http://localhost:8080
set -euo pipefail

BASE="${1:?URL de base attendue (ex. http://localhost:8080)}"
API="$BASE/api"
PASS=0; FAIL=0
TS=$(date +%s)

ok()   { PASS=$((PASS+1)); echo "  OK  $1"; }
ko()   { FAIL=$((FAIL+1)); echo "  FAIL $1"; }
check(){ # check <description> <expected> <actual>
  if [ "$2" = "$3" ]; then ok "$1 ($3)"; else ko "$1 — attendu « $2 », obtenu « $3 »"; fi
}

req() { # req <method> <path> [token] [json_body] -> body on stdout, code in $CODE
  local method="$1" path="$2" token="${3:-}" body="${4:-}"
  local args=(-s -o /tmp/resp.json -w '%{http_code}' -X "$method" "$API$path" -H 'Accept: application/json' -H 'Content-Type: application/json')
  [ -n "$token" ] && args+=(-H "Authorization: Bearer $token")
  [ -n "$body" ] && args+=(--data "$body")
  CODE=$(curl "${args[@]}")
  cat /tmp/resp.json
}

echo "== Parcours MOVENT sur $BASE =="

# --- 1. Inscription + doublons ----------------------------------------------
echo "-- Inscription --"
req POST /auth/register "" "{\"first_name\":\"Test\",\"last_name\":\"E2E\",\"email\":\"e2e-$TS@movent.test\",\"password\":\"Password123!\",\"role\":\"PARTICIPANT\"}" >/dev/null
check "Inscription nouveau participant" 201 "$CODE"
req POST /auth/register "" "{\"first_name\":\"Test\",\"last_name\":\"E2E\",\"email\":\"e2e-$TS@movent.test\",\"password\":\"Password123!\"}" >/dev/null
check "Refus e-mail déjà utilisé" 422 "$CODE"
req POST /auth/register "" "{\"first_name\":\"Root\",\"last_name\":\"Hack\",\"email\":\"hack-$TS@movent.test\",\"password\":\"Password123!\",\"role\":\"ADMIN\"}" >/dev/null
check "Refus auto-inscription ADMIN" 422 "$CODE"

# --- 2. Comptes de démonstration ----------------------------------------------
echo "-- Comptes de démonstration --"
for cred in "participant1@movent.test:PT" "organizer1@movent.test:OT" "admin@movent.test:AT" "autorite@movent.test:NT"; do
  email="${cred%%:*}"; var="${cred##*:}"
  req POST /auth/login "" "{\"email\":\"$email\",\"password\":\"Password123!\"}" >/dev/null
  check "Login $email" 200 "$CODE"
  eval "$var=\"\$(jq -r '.token // empty' /tmp/resp.json)\""
done

# --- 3. Gouvernance : autorité = lecture seule ---------------------------------
echo "-- Supervision nationale --"
req GET /users "$NT" >/dev/null
check "Autorité : GET /users" 200 "$CODE"
req GET /admin/events "$NT" >/dev/null
check "Autorité : GET /admin/events" 200 "$CODE"
req GET /event-stats "$NT" >/dev/null
check "Autorité : GET /event-stats" 200 "$CODE"
req GET /users "$AT" >/dev/null
SOME_USER=$(jq -r '.data[0].id // empty' /tmp/resp.json)
[ -n "$SOME_USER" ] && {
  req PATCH "/users/$SOME_USER/role" "$NT" '{"role":"ADMIN"}' >/dev/null
  check "Autorité : PATCH role refusé" 403 "$CODE"
}
req GET /users "$PT" >/dev/null
check "Participant : GET /users refusé" 403 "$CODE"

# --- 4. Cycle événement : création → soumission → validation -------------------
echo "-- Événement --"
CAT_ID=$(req GET /categories "" | jq -r '.[0].id')
START=$(date -u -d "+10 days" +%Y-%m-%dT%H:%M:%SZ)
END=$(date -u -d "+10 days +2 hours" +%Y-%m-%dT%H:%M:%SZ)
EVENT_BODY="{\"title\":\"E2E Soirée $TS\",\"description\":\"Événement de test\",\"category_id\":\"$CAT_ID\",\"location\":\"Lomé\",\"start_at\":\"$START\",\"end_at\":\"$END\",\"capacity\":\"5\",\"price\":\"5000\",\"organizer_kind\":\"INSTITUTION\"}"
req POST /events "$OT" "$EVENT_BODY" >/dev/null
check "Organisateur : création brouillon" 201 "$CODE"
EV_ID=$(jq -r '.id' /tmp/resp.json)
check "Type INSTITUTION enregistré" "INSTITUTION" "$(jq -r '.organizer_kind' /tmp/resp.json)"
req GET "/events/$EV_ID" "" >/dev/null
check "Brouillon invisible publiquement" 404 "$CODE"
req POST "/events/$EV_ID/submit" "$OT" >/dev/null
check "Soumission → PENDING" 200 "$CODE"
req POST "/events/$EV_ID/approve" "$NT" >/dev/null
check "Autorité : approve refusé" 403 "$CODE"
req POST "/events/$EV_ID/approve" "$AT" >/dev/null
check "Admin : approve → APPROVED" 200 "$CODE"
req GET "/events/$EV_ID" "" >/dev/null
check "Approuvé visible publiquement" 200 "$CODE"

# --- 5. Réservation + paiement --------------------------------------------------
echo "-- Réservation & paiement --"
req POST /bookings "$PT" "{\"event_id\":\"$EV_ID\",\"quantity\":2}" >/dev/null
check "Création réservation (payante)" 201 "$CODE"
BK_ID=$(jq -r '.id' /tmp/resp.json)
check "Réservation PENDING avant paiement" "PENDING" "$(jq -r '.status' /tmp/resp.json)"
req POST "/bookings/$BK_ID/pay" "$PT" '{"outcome":"success"}' >/dev/null
check "Paiement réussi → CONFIRMED" "CONFIRMED" "$(jq -r '.status' /tmp/resp.json)"
NB_TICKETS=$(jq '.tickets | length' /tmp/resp.json)
[ "$NB_TICKETS" = "2" ] && ok "2 billets générés" || ko "Billets générés : $NB_TICKETS (attendu 2)"
TICKET_ID=$(jq -r '.tickets[0].id' /tmp/resp.json)
QR1=$(jq -r '.tickets[0].qr_token' /tmp/resp.json); QR2=$(jq -r '.tickets[1].qr_token' /tmp/resp.json)
[ -n "$QR1" ] && [ "$QR1" != "$QR2" ] && ok "QR uniques" || ko "QR manquants ou dupliqués"
CODE=$(curl -s -o /tmp/ticket.pdf -w '%{http_code}' "$API/tickets/$TICKET_ID/download" -H "Authorization: Bearer $PT")
check "Téléchargement PDF billet" 200 "$CODE"
head -c 5 /tmp/ticket.pdf | grep -q '%PDF' && ok "Le fichier est un vrai PDF" || ko "Pas un PDF"

# paiement échoué sur une 2e réservation
req POST /bookings "$PT" "{\"event_id\":\"$EV_ID\",\"quantity\":1}" >/dev/null
BK2=$(jq -r '.id' /tmp/resp.json)
req POST "/bookings/$BK2/pay" "$PT" '{"outcome":"failed"}' >/dev/null
check "Paiement échoué → FAILED" "FAILED" "$(jq -r '.status' /tmp/resp.json)"

# places : capacité 5, 2 confirmées + 1 échouée (libérée) → 3 restantes
req POST /bookings "$PT" "{\"event_id\":\"$EV_ID\",\"quantity\":11}" >/dev/null
check "Quantité > 10 refusée (validation)" 422 "$CODE"
req POST /bookings "$PT" "{\"event_id\":\"$EV_ID\",\"quantity\":4}" >/dev/null
check "Places insuffisantes → 409" 409 "$CODE"

# --- 6. Sécurité ------------------------------------------------------------------
echo "-- Sécurité --"
req POST "/events/$EV_ID/hold" "" '{"booking_id":"00000000-0000-4000-8000-000000000099","quantity":1}' >/dev/null
check "Route interne hold bloquée par la passerelle" 403 "$CODE"
req GET "/tickets/$TICKET_ID" "$PT" >/dev/null
check "Propriétaire voit son billet" 200 "$CODE"
req POST /auth/login "" '{"email":"participant2@movent.test","password":"Password123!"}' >/dev/null
PT2=$(jq -r '.token' /tmp/resp.json)
req GET "/tickets/$TICKET_ID" "$PT2" >/dev/null
check "Billet d'un autre → 404" 404 "$CODE"
req GET "/bookings/$BK_ID" "$PT2" >/dev/null
check "Réservation d'un autre → 404" 404 "$CODE"

# --- 7. Pages frontend ----------------------------------------------------------------
echo "-- Pages --"
for p in / /events /login /register /supervision; do
  CODE=$(curl -s -o /dev/null -w '%{http_code}' "$BASE$p")
  check "Page $p" 200 "$CODE"
done

echo ""
echo "=== Résultat : $PASS succès, $FAIL échecs ==="
[ "$FAIL" = "0" ]
