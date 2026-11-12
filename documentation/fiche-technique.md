# Fiche technique — MOVENT

| Rubrique | Détail |
|---|---|
| Produit | Infrastructure numérique nationale de gestion événementielle, destinée à intégrer le patrimoine numérique de l'État togolais (sous réserve de formalisation juridique et institutionnelle) |
| Architecture | Microservices : User, Event, Booking + Frontend + API Gateway |
| Langage / framework | PHP 8.3 / Laravel 11 (Blade côté frontend) |
| Base de données | PostgreSQL 16, **une base par service**, volumes persistants |
| Conteneurisation | Docker + Docker Compose (8 conteneurs, réseau `movent-network`) |
| Passerelle | Nginx 1.27 (alpine), port public `GATEWAY_PORT` (8080 par défaut) |
| Authentification | JWT HS256 (firebase/php-jwt), 8 h, vérifié localement par chaque service |
| Rôles | ADMIN (exploitation), NATIONAL_AUTHORITY (supervision nationale en lecture seule), ORGANIZER, PARTICIPANT (contrôle serveur) |
| Type d'événement | `organizer_kind` : INSTITUTION (public) ou PRIVATE (privé), filtrable au catalogue |
| Paiement | Simulé (succès/échec), `method = SIMULATED` |
| Billets | PDF (barryvdh/laravel-dompdf) + QR code (endroid/qr-code), jeton unique |
| Identifiants | UUID ; pas de clé étrangère entre bases |
| Concurrence | Transaction + `SELECT … FOR UPDATE` pour le blocage des places |

## Conteneurs et ports internes

| Conteneur | Port interne | Dépend de |
|---|---|---|
| movent-gateway | 80 (publié sur 8080) | frontend, services |
| movent-frontend | 8000 | — |
| movent-user-service | 8000 | movent-db-users |
| movent-event-service | 8000 | movent-db-events |
| movent-booking-service | 8000 | movent-db-bookings, event-service |
| movent-db-users / events / bookings | 5432 (non publié) | — |

## Variables d'environnement (`.env`)

| Variable | Rôle |
|---|---|
| `GATEWAY_PORT` | Port public de la plateforme |
| `JWT_SECRET` | Secret partagé de signature/vérification des JWT (≥ 32 caractères) |
| `INTERNAL_API_KEY` | Clé des appels internes Booking → Event |
| `USERS_DB_PASSWORD` · `EVENTS_DB_PASSWORD` · `BOOKINGS_DB_PASSWORD` | Mots de passe PostgreSQL |
| `HOLD_MINUTES` | Durée de blocage des places pendant le paiement (10 par défaut) |
| `APP_KEY` | Facultatif : générée automatiquement au démarrage |

## Sécurité
Bcrypt, JWT à expiration, rôles + propriété vérifiés côté serveur, validation de toutes les entrées, throttling login/inscription, routes internes protégées (clé + gateway), secrets hors dépôt (`.env` ignoré), échappement HTML côté interface.

## Sauvegarde / restauration
`pg_dump` / `psql` par conteneur (commandes dans le README, § 7). Volumes : `users_data`, `events_data`, `bookings_data`.
