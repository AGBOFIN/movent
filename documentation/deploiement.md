# Déploiement de MOVENT sur une VM (production)

Guide pas à pas pour héberger MOVENT sur une petite VM (2 vCPU / 4 Go RAM suffisent), par exemple Hetzner CX22 ou Scaleway DEV1-S. Durée typique : 20 minutes hors build.

## 0. Ce dont vous avez besoin

- Une VM Ubuntu 22.04/24.04 avec un **nom de domaine** pointant vers son IP (recommandé pour le HTTPS automatique) **ou** simplement son **adresse IP** (HTTP seul).
- Pas de domaine ? Deux options :
  - **DuckDNS** (gratuit) : créez un sous-domaine `movent-xxxx.duckdns.org` qui suit l'IP de la VM → HTTPS automatique chez Caddy.
  - **IP directe** : `DOMAIN=` l'IP de la VM dans `.env` → le site est accessible en `http://IP` (pas de HTTPS, suffisant pour une démo).

## 1. Créer la VM et ouvrir les ports

Chez l'hébergeur (Hetzner/Scaleway) :

1. Créez un serveur Ubuntu, ajoutez votre clé SSH.
2. **Pare-feu** : n'ouvrez que les ports **22** (SSH), **80** (HTTP) et **443** (HTTPS). Hetzner : console cloud → Firewall ; Scaleway : *Security Group* → inbound TCP 22/80/443. Les bases de données ne sont **pas** exposées (réseau Docker interne uniquement).

## 2. Se connecter et installer Docker

```bash
ssh root@IP_DE_LA_VM          # ou ubuntu@…

# Docker + plugin Compose
curl -fsSL https://get.docker.com | sh

# Vérification
docker --version && docker compose version
```

## 3. Récupérer le code

```bash
# Dépôt public : git clone <URL_DU_DEPOT> movent && cd movent
# ou dépôt privé : git clone git@github.com:VOUS/movent.git && cd movent
# ou envoyez le dossier : scp -r movent root@IP:/root/
```

## 4. Configurer les secrets

```bash
cp .env.example .env
nano .env        # ou vim .env
```

À renseigner **obligatoirement** :

| Variable | Valeur |
|---|---|
| `DOMAIN` | Votre domaine (`movent.exemple.com`) **ou** l'IP de la VM pour du HTTP simple |
| `JWT_SECRET` | `openssl rand -hex 32` |
| `INTERNAL_API_KEY` | `openssl rand -hex 32` |
| `USERS_DB_PASSWORD` / `EVENTS_DB_PASSWORD` / `BOOKINGS_DB_PASSWORD` | 3 mots de passe forts et **différents** |
| `ACME_EMAIL` | optionnel (notifications certificats) |

`APP_KEY` reste vide : une clé est générée automatiquement au démarrage.

## 5. Lancer la plateforme

```bash
chmod +x deploy.sh   # une seule fois (ou lancez : bash deploy.sh)
./deploy.sh
# équivalent manuel :
docker compose -f docker-compose.prod.yml up -d --build
```

Le premier build télécharge PHP, Composer et les dépendances Laravel : comptez **10 à 20 minutes** sur 2 vCPU. Ensuite, chaque service attend sa base, exécute `migrate --force` puis `db:seed --force` automatiquement : les comptes de démonstration et les 10 événements sont créés au premier démarrage.

## 6. Vérifications

```bash
# Les 8 conteneurs doivent être « Up » (les 4 apps peuvent être « health: starting » ~1 min)
docker compose -f docker-compose.prod.yml ps

# Logs en cas de souci
docker compose -f docker-compose.prod.yml logs -f caddy
docker compose -f docker-compose.prod.yml logs user-service

# Test API via la passerelle
curl http://localhost/api/events          # liste JSON des événements validés
curl -I http://localhost/                 # le frontend (suivez la redirection HTTPS si domaine)
```

Puis ouvrez `https://VOTRE-DOMAINE` (ou `http://IP`) dans un navigateur : la landing page MOVENT s'affiche, le catalogue est peuplé. Comptes de démo dans le README (§8), mot de passe `Password123!`.

**HTTPS** : avec un domaine, Caddy obtient et renouvelle le certificat seul (ports 80/443 doivent être joignables depuis Internet). Comptez ~30 s après le démarrage.

## 7. Sauvegarde des bases

```bash
# Sauvegarde complète (à lancer régulièrement ou avant la soutenance)
cd movent
docker compose -f docker-compose.prod.yml exec user-db    pg_dump -U movent movent_users    > users.sql
docker compose -f docker-compose.prod.yml exec event-db    pg_dump -U movent movent_events   > events.sql
docker compose -f docker-compose.prod.yml exec booking-db  pg_dump -U movent movent_bookings > bookings.sql

# Restauration
docker compose -f docker-compose.prod.yml exec -T user-db    psql -U movent movent_users    < users.sql
docker compose -f docker-compose.prod.yml exec -T event-db    psql -U movent movent_events   < events.sql
docker compose -f docker-compose.prod.yml exec -T booking-db  psql -U movent movent_bookings < bookings.sql
```

Les volumes Docker (`users_data`, `events_data`, `bookings_data`) persistent les données entre redémarrages et `docker compose down`.

## 8. Mises à jour du code

```bash
cd movent
git pull                       # ou copiez les fichiers modifiés
./deploy.sh                    # rebuild + redémarrage (migrations auto au boot)
```

## 9. Arrêt et suppression après la soutenance

```bash
# Arrêt en gardant les données
docker compose -f docker-compose.prod.yml down

# ARRÊT DÉFINITIF + effacement des données (irréversible)
docker compose -f docker-compose.prod.yml down -v
# Puis supprimez la VM chez l'hébergeur.
```

## 10. Dépannage rapide

| Symptôme | Vérification |
|---|---|
| Site inaccessible | `docker compose -f docker-compose.prod.yml ps` ; ports 80/443 ouverts côté hébergeur ; `logs caddy` |
| Pas de HTTPS | `DOMAIN` doit être un **nom de domaine** (pas une IP) ; ports 80+443 joignables ; `logs caddy` pour les erreurs ACME |
| 502 Bad Gateway | un service Laravel est en panne : `logs <service>` ; souvent la base pas encore prête (patience au 1er boot) |
| Plus de place disque | `docker system prune -af` (supprime les images inutilisées — les volumes de données sont conservés) |
| RAM saturée | `docker stats` ; les 4 apps Laravel + 3 PostgreSQL tiennent dans ~2 Go sur 4 Go |

## Architecture déployée

Voir `documentation/architecture.md` : 8 conteneurs (frontend, caddy, 3 microservices Laravel, 3 PostgreSQL) sur un réseau Docker interne ; Caddy est le seul à exposer des ports.
