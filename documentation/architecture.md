# MOVENT — Architecture technique

Document de référence pour l'oral et la maintenance. Il complète le README (vue fonctionnelle) par les **décisions techniques** et leurs justifications.

## 1. Vue d'ensemble

MOVENT est une application Laravel découpée en **4 applications Laravel 11** (PHP 8.3) et **3 bases PostgreSQL 16**, le tout orchestré par Docker Compose derrière une **gateway Nginx** :

| Composant | Conteneur | Rôle |
|---|---|---|
| Frontend | `movent-frontend` | Pages Blade publiques + 3 espaces ; consomme l'API via la gateway |
| Gateway | `movent-gateway` | Unique point d'entrée HTTP ; route `/api/*` vers le bon service ; **bloque les routes internes** |
| User Service | `movent-user-service` | Inscription, connexion, JWT, profil, gestion des utilisateurs |
| Event Service | `movent-event-service` | Catégories, événements, cycle de validation, **holds atomiques de places** |
| Booking Service | `movent-booking-service` | Réservations, paiement simulé, billets (QR + PDF Dompdf), statistiques |
| 3 × PostgreSQL | `movent-db-*` | Une base par service : `movent_users`, `movent_events`, `movent_bookings` |

Le code de chaque service vit dans `services/<nom>/overlay/` ; l'image Docker (`docker/laravel.Dockerfile`) crée un squelette Laravel 11 propre puis **superpose** l'overlay et le code partagé `services/_shared/overlay/` (middlewares JWT/rôle/clé interne, bootstrap, entrypoint).

## 2. Décisions et justifications

### 2.1 Authentification : JWT HS256 vérifié localement

- Le **User Service est la seule autorité d'émission** : `POST /api/auth/login` et `/register` retournent un JWT signé `HS256` avec `JWT_SECRET` (claims : `sub`, `role`, `email`, `name`, `iat`, `exp` = 8 h).
- Les autres services **vérifient la signature localement** avec le même secret (`App\Http\Middleware\JwtAuth`, bibliothèque `firebase/php-jwt`).

**Pourquoi pas Sanctum ?** Sanctum est conçu pour une seule application (tokens en base ou SPA stateful). Ici, trois services distincts avec trois bases doivent vérifier le même jeton : un JWT autoporteur évite tout appel réseau par requête et tout état partagé.

**Pourquoi pas un appel User Service à chaque requête ?** Cela créerait un point de défaillance unique et ajouterait une latence à chaque appel ; la vérification locale est O(1) et sans état.

**Limites assumées** : pas de révocation — un compte désactivé ou un jeton volé reste valide jusqu'à `exp` (8 h). Pour le MVP c'est acceptable ; l'évolution naturelle est une liste de révocation ou des jetons courts + refresh.

### 2.2 Autorisation : rôle dans le JWT + propriété de la ressource

Quatre rôles : `PARTICIPANT`, `ORGANIZER`, `ADMIN` (exploitation) et **`NATIONAL_AUTHORITY`** (supervision nationale, **lecture seule**). L'autorité nationale accède aux mêmes données de consultation que l'admin (`GET /users`, `/admin/events`, `/event-stats`, `/organizer/bookings`, `/booking-stats`) mais **aucune route d'écriture** ne lui est ouverte : la modération (`approve`/`reject`), la gestion des utilisateurs et la gestion des événements restent réservées à `ADMIN`. Elle ne peut pas s'attribuer ce rôle (inscription limitée à PARTICIPANT/ORGANIZER ; attribution par ADMIN uniquement).

- `events.organizer_kind` (`INSTITUTION` | `PRIVATE`) distingue les événements d'organisateurs publics (ministères, administrations, collectivités, cérémonies officielles) des privés (entreprises, associations, agences, indépendants) ; il est exposé dans le catalogue (badge + filtre) et dans la supervision.
- `RoleCheck` (`role:ADMIN`, `role:ORGANIZER,ADMIN,NATIONAL_AUTHORITY`…) lit `auth.role` posé par `JwtAuth` ; les routes d'écriture listent explicitement les rôles autorisés.
- La **propriété** est vérifiée en base : `EventController::own()` compare `organizer_id` (UUID externe) au `sub` du jeton ; `BookingController::mine()` et `TicketController::mine()` filtrent sur `participant_id`. Un participant ne peut jamais lire le billet d'un autre (404, pas 403, pour ne pas révéler l'existence).

### 2.3 Isolation des données : UUID inter-services, FK intra-base uniquement

- Chaque service a sa base ; **aucune clé étrangère ne traverse les bases**. `events.organizer_id`, `holds.booking_id`, `bookings.participant_id/event_id/organizer_id` sont des UUID sans contrainte.
- Les FK PostgreSQL n'existent qu'à l'intérieur d'une base : `events.category_id → categories`, `holds.event_id → events`, `payments.booking_id`, `tickets.booking_id`, **`events.venue_id → venues`** (relation interne à la base event-service).
- **Table `venues`** (même base que `events`) : lieux réels d'événements au Togo (hôtels, palais des congrès, stades, centres culturels) avec nom, type, ville, adresse, description, capacité publique, équipements, site officiel, photo et crédit d'image. Exposée publiquement en lecture via `/api/venues` ; chaque événement seedé est rattaché à un lieu, et le catalogue filtre par `venue_id` et `city`.
- **Dénormalisation assumée** dans `bookings` : `participant_name`, `event_title`, `event_start_at`, `event_location`, `organizer_id` sont des instantanés au moment de la réservation. Ils évitent des appels inter-services à chaque affichage et figent l'historique si l'événement change. C'est la contrepartie logique de l'absence de FK : l'intégrité est assurée par les API, pas par la base.

### 2.4 Anti-surréservation : hold atomique dans PostgreSQL

```
POST /api/events/{id}/hold   (interne, X-Internal-Key)
  └─ DB::transaction
       ├─ Hold::expireStale()          # libère les holds échus
       ├─ Event::lockForUpdate()->find()   # SELECT … FOR UPDATE
       ├─ vérif status APPROVED + end_at futur
       ├─ vérif available_places >= quantity  → sinon 409
       ├─ available_places -= quantity
       └─ INSERT holds (HELD, expires_at = now + HOLD_MINUTES)
```

Deux requêtes simultanées pour les dernières places sont **sérialisées par le verrou de ligne** `FOR UPDATE` sur `events` : la seconde lit `available_places` déjà décrémenté et reçoit 409. `PHP_CLI_SERVER_WORKERS=4` permet des requêtes réellement concurrentes dans les conteneurs.

Cycle de vie d'un hold : `HELD → CONFIRMED` (paiement réussi / événement gratuit) · `HELD|CONFIRMED → RELEASED` (échec, annulation) · `HELD → EXPIRED` (délai dépassé, libéré à la demande par `expireStale()`).

Les routes internes (`hold`, `confirm-hold`, `release-hold`) sont **doublement protégées** : en-tête `X-Internal-Key` (comparaison `hash_equals`) côté service **et** refus 403 au niveau de la gateway Nginx — elles ne sont jamais exposées publiquement.

### 2.5 Réservation et paiement simulé (Booking Service)

```
POST /api/bookings
  1. GET  event-service /api/events/{id}   → snapshot titre/dates/lieu/prix/organizer
  2. POST event-service /hold              → places bloquées HOLD_MINUTES
  3. INSERT bookings (PENDING)
  4. si prix = 0 → confirm-hold + CONFIRMED + billets immédiats
POST /api/bookings/{id}/pay {outcome}
  success → confirm-hold → Payment SUCCESS + booking CONFIRMED + N tickets (QR unique)
  failed  → release-hold  → Payment FAILED  + booking FAILED (places rendues)
```

- Le **prix est toujours relu côté Event Service** au moment de la réservation : le client ne choisit jamais le montant.
- **Aucun billet pour une réservation non confirmée** : `makeTickets()` n'est appelé qu'après `confirm-hold` réussi, dans la même transaction que le passage à CONFIRMED.
- QR : `bin2hex(random_bytes(24))` (48 hex chars), colonne `UNIQUE` — un qr_token ne peut exister qu'une fois.
- PDF : Dompdf + vue Blade `ticket` (QR en data-URI PNG via `endroid/qr-code`).
- Reprise sur erreur : si l'INSERT booking échoue après le hold, `release-hold` est appelé pour ne pas perdre les places.

### 2.6 Passerelle : Nginx en local, Caddy en production

**Local** (`docker-compose.yml`) : gateway Nginx `movent-gateway`, port `${GATEWAY_PORT:-8080}`.

**Production** (`docker-compose.prod.yml`) : **Caddy** `movent-caddy` remplace Nginx — même routage `/api/*` (bloc `handle` par service dans `caddy/Caddyfile`), mais avec **HTTPS automatique** (Let's Encrypt) dès que `DOMAIN` est un nom de domaine. Si `DOMAIN` est une adresse IP, Caddy sert en HTTP:80 (fallback sans domaine). Seuls les ports **80/443** sont exposés.

Règles de routage (identiques dans les deux passerelles) :

| Préfixe | Destination |
|---|---|
| `/api/auth`, `/api/me`, `/api/users` | user-service |
| `/api/events`, `/api/my-events`, `/api/categories`, `/api/event-stats`, `/api/admin/events`, `/api/venues` | event-service |
| `/api/bookings`, `/api/tickets`, `/api/organizer`, `/api/booking-stats` | booking-service |
| `/api/events/{id}/hold|confirm-hold|release-hold` | **403** (interne) |
| `/api/*` inconnu | 404 JSON |
| tout le reste | frontend (pages Blade) |

Le chemin de la requête est **conservé** (pas de réécriture) : les services Laravel écoutent déjà sous `/api`. En production, le certificat TLS est renouvelé automatiquement par Caddy ; les certificats sont stockés dans les volumes `caddy_data`/`caddy_config`.

### 2.7 Choix transverses

- **UUID partout** (`HasUuids`) : identifiants non devinables, pas de fuite du nombre d'enregistrements.
- **Contraintes SQL** : `CHECK` PostgreSQL (quantités > 0, `available_places ≤ capacity`, montants ≥ 0), unicité e-mail / références / qr_token, `NUMERIC(12,2)`, index sur statuts/dates/organizer_id/participant_id.
- **Erreurs uniformes** : JSON `{message}` ou erreurs de validation Laravel `{message, errors}` ; 401 non authentifié, 403 mauvais rôle, 404 ressource d'autrui (ne pas révéler), 409 conflit métier, 410 hold expiré, 502 service interne indisponible.
- **Validation** : `FormRequest`-like via `$request->validate()` avec règles strictes dans chaque contrôleur.
- **Migrations + seeders idempotents** : `firstOrNew`/`firstOrCreate` et UUID fixes partagés entre services (les seeders booking/event référencent les organisateurs du user-service).

## 3. Flux de données clés

Voir les diagrammes dans `documentation/diagrammes/` :
- `architecture-globale.mmd` — conteneurs et flux
- `sequence-reservation.mmd` — réservation + paiement simulé
- `sequence-validation.mmd` — cycle de validation d'un événement
- `modele-donnees.mmd` — tables par base

## 4. Limites connues (assumées pour le MVP)

- JWT sans révocation (cf. 2.1) ; un compte désactivé reste valide jusqu'à expiration du jeton.
- Libération des holds « à la demande » (`expireStale()` appelé sur les lectures/holds) — pas de scheduler.
- Annuler un événement APPROVED ne rembourse/annule pas les réservations existantes (hors périmètre).
- Le paiement est simulé : `outcome` choisi par le client, `method = SIMULATED`.
- Tests PHPUnit exécutés en SQLite (`:memory:`) dans les conteneurs : les `CHECK` PostgreSQL ne sont pas couverts par les tests (ils restent actifs en production).
