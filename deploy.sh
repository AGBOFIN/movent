#!/usr/bin/env bash
# MOVENT — déploiement / mise à jour sur la VM de production.
# Usage :  ./deploy.sh          (build + (re)démarrage)
#         DOMAIN=movent.ex.com ./deploy.sh   (override .env pour un test)
set -euo pipefail
cd "$(dirname "$0")"

COMPOSE="docker compose -f docker-compose.prod.yml"

echo "==> Vérification du fichier .env"
if [ ! -f .env ]; then
  echo "ERREUR : .env introuvable. Copiez .env.example vers .env et remplissez-le." >&2
  exit 1
fi
if grep -qE 'a-changer|change-me|change_me' .env; then
  echo "ERREUR : .env contient encore des valeurs par défaut (a-changer/change-me)." >&2
  echo "       Renseignez DOMAIN, JWT_SECRET, INTERNAL_API_KEY et les 3 mots de passe DB." >&2
  exit 1
fi

echo "==> Build et démarrage des 8 conteneurs"
$COMPOSE up -d --build

echo "==> État des conteneurs"
$COMPOSE ps

cat <<'EOF'

Terminé. Vérifications utiles :
  $ docker compose -f docker-compose.prod.yml logs -f caddy
  $ docker compose -f docker-compose.prod.yml logs user-service event-service booking-service
  $ curl -I https://VOTRE-DOMAINE/      (ou http://IP-DE-LA-VM/ si DOMAIN est une IP)

Les migrations et les données de démonstration sont exécutées automatiquement
au premier démarrage de chaque service (entrypoint : migrate --force + db:seed).
EOF
