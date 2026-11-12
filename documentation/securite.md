# Sécurité MOVENT

État des protections mises en place et de leurs limites connues.

## Authentification et sessions

| Mécanisme | Implémentation | Emplacement |
|---|---|---|
| Mots de passe | Hashés (bcrypt via Laravel `Hash`) | `user-service` — `AuthController` |
| Jetons | JWT **HS256**, claims `sub`, `role`, `email`, `name`, `iat`, `exp` (8 h) | `user-service` émet ; `services/_shared` `JwtAuth` vérifie localement dans event/booking/frontend |
| Déconnexion | Suppression du jeton côté client ; jeton stateless sans révocation serveur (voir limites) | `app.js` |
| Rate limiting | `throttle:20,1` sur `/api/auth/login` et `/api/auth/register` | routes user-service |
| CSRF | Non applicable : API JSON-only, jeton en `Authorization: Bearer` (pas de cookie de session) | — |
| Cookies | Aucun cookie d'authentification utilisé → rien à marquer Secure/HttpOnly | — |

## Contrôle d'accès par rôle (serveur, jamais seulement masqué côté UI)

| Rôle | Droits |
|---|---|
| `PARTICIPANT` | Réservation/paiement/annulation de **ses** réservations, billets, profil |
| `ORGANIZER` | CRUD de **ses** événements uniquement (`own()` vérifie `organizer_id`), soumission,annulation |
| `ADMIN` | Modération (approbation/rejet), stats, supervision événements |
| `NATIONAL_AUTHORITY` | **Lecture seule** : stats événements, supervision utilisateurs/réservations/tickets. Écritures refusées : `role:` middleware sur toutes les routes d'écriture booking, et absence de routes d'écriture event/user |

Points vérifiés :

- **IDOR** : `BookingController::mine()` filtre par `participant_id` du JWT ; `TicketController` idem ; `EventController::own()` filtre par `organizer_id`. Testé dans `BookingFlowTest` (paiement/lecture par un autre participant → 404).
- **Autorité nationale en écriture** : `POST /api/bookings`, `/pay`, `/cancel` exigent `role:PARTICIPANT,ORGANIZER,ADMIN` → 403 pour `NATIONAL_AUTHORITY` (test dédié).
- **Routes internes** (`hold`, `confirm-hold`, `release-hold`) : double protection — blocage nginx/Caddy (`return 403`) **et** middleware `InternalKey` (`X-Internal-Key` comparé via `hash_equals`).
- `INTERNAL_API_KEY` ne quitte jamais les services ; la gateway ne la transmet pas, le navigateur ne la voit jamais.

## Validation et injections

- Toutes les entrées validées via `$request->validate()` (UUID, dates, enums `in:`, bornes numériques).
- Requêtes Eloquent/Query Builder paramétrées → pas d'injection SQL.
- Sorties Blade échappées (`{{ }}`), contenu JS échappé via `esc()` → pas de XSS réfléchi.
- Anti-survente : `SELECT ... FOR UPDATE` + transaction PostgreSQL sur `available_places`.

## En-têtes HTTP

Posés par la **gateway** (nginx local, Caddy en prod) sur toutes les réponses :

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Content-Security-Policy` : `default-src 'self'`, scripts `'self'` uniquement, styles `'self' 'unsafe-inline'` + Google Fonts, images `'self' data:`
- `Strict-Transport-Security` : **uniquement derrière HTTPS** (Caddy prod ; nginx local en HTTP ne l'émet pas — voir note ci-dessous)

> Note : l'en-tête HSTS dans `gateway/nginx.conf` est émis même en HTTP local ; les navigateurs l'ignorent hors HTTPS, donc sans effet, mais il sera actif tel quel si la gateway est un jour servie en TLS. En prod c'est Caddy qui le pose.

## Configuration

- `APP_DEBUG=false` en production (voir `.env.example` / `docker-compose.prod.yml`).
- Aucune clé secrète dans le dépôt : `JWT_SECRET`, `INTERNAL_API_KEY` viennent des variables d'environnement.
- Mots de passe de démo (`Password123!`) uniquement en seeders, jamais en production.

## Limites connues

- **JWT sans liste de révocation** : un jeton volé reste valide jusqu'à `exp` (8 h). Mitigation possible : réduire `exp`, ou ajouter une denylist (à évaluer pour la démo).
- **Paiement simulé** : `outcome` choisi par le client — assumé pour le MVP, à remplacer par un PSP réel.
- **CSP `style-src 'unsafe-inline'`** : requis par les attributs `style=` générés en JS ; à durcir si le front passe en CSS classes.
- **Upload d'images** : aucun endpoint d'upload n'existe (les images sont des URLs validées `nullable|url|max:500` ou des assets statiques) → pas de surface d'upload à restreindre pour l'instant.
