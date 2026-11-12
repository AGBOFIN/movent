# Guide d'utilisation — MOVENT

Adresse : l'URL de la plateforme (en local : http://localhost:8080). Comptes de démonstration dans le README (mot de passe `Password123!`).

## Pour un participant
1. **Créer un compte** : « S'inscrire », choisir *Participant*, renseigner nom, e-mail, mot de passe (8 caractères minimum). Puis **se connecter**.
2. **Chercher un événement** : « Catalogue » → recherche par mot-clé, filtre par catégorie, case « Gratuits uniquement ». Cliquer sur une carte pour voir le détail (date, lieu, prix, places restantes).
3. **Réserver** : choisir le nombre de places puis « Réserver ». Événement gratuit : confirmation immédiate. Événement payant : fenêtre de **paiement simulé** → « Simuler un paiement réussi » ou « échoué ». Les places sont bloquées environ 10 minutes ; au-delà, la réservation expire.
4. **Retrouver ses réservations** : *Mon espace* → « Mes réservations » (statut, référence, montant). Une réservation en attente peut être payée ou annulée.
5. **Télécharger ses billets** : « Mes billets » → « Télécharger le PDF » (QR code unique). Aucun billet n'est généré pour un paiement échoué.
6. **Modifier son profil** : onglet « Profil ».

## Pour un organisateur
1. S'inscrire en choisissant *Organisateur* (ou utiliser un compte de démonstration), puis ouvrir **Mon espace**.
2. **Créer un événement** : « + Nouvel événement » → titre, catégorie, description, lieu, dates, capacité, prix (0 = gratuit). L'événement est enregistré en **brouillon**.
3. **Soumettre à validation** : bouton « Soumettre » ; le statut passe à *En attente*.
4. **Suivre la validation** : *Validé* → visible publiquement ; *Rejeté* → le motif s'affiche, corriger via « Modifier » puis soumettre à nouveau.
5. **Consulter les réservations** et les statistiques (événements, billets vendus, revenus) dans l'onglet « Réservations » et le bandeau de chiffres.

## Pour une autorité nationale
1. Se connecter avec un compte `NATIONAL_AUTHORITY` (ex. `autorite@movent.test`) → **Supervision nationale**.
2. Consulter les **statistiques consolidées** : utilisateurs, événements par statut, réservations, billets émis, revenus.
3. Parcourir **tous les événements** du territoire (statut, type Institution/Privé, dates, places), **tous les utilisateurs** et **toutes les réservations**.
4. Accès **strictement en lecture seule** : la modération et la gestion restent réservées à l'administrateur.

## Pour un administrateur
1. Se connecter avec un compte ADMIN → **Administration**.
2. **Modérer** : onglet « Modération » → examiner chaque demande puis « Valider » ou « Rejeter » (motif obligatoire).
3. **Utilisateurs** : changer le rôle (ADMIN / ORGANIZER / PARTICIPANT) ou activer/désactiver un compte.
4. **Événements** : liste de tous les événements et de leur statut.
5. **Réservations et statistiques** : toutes les réservations, billets vendus, revenus.

## En cas de problème
- « Authentification requise » : se reconnecter (le token dure 8 h).
- « Places insuffisantes » : réduire la quantité ou choisir un autre événement.
- « Le délai de paiement est dépassé » : refaire la réservation.
