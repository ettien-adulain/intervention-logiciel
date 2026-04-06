# Découpage par phases — Cahier des charges & base `intervention_bd`

Document de référence : alignement entre le **cahier des charges** (`cahier_charge.txt`) et le schéma **MySQL** (`intervention_bd.sql`). Chaque phase décrit le travail à réaliser et les **tables concernées** ainsi que les **relations logiques** entre elles.

---

## Vue d’ensemble du modèle de données (relations)

| Table | Rôle | Liens principaux |
|--------|------|------------------|
| `clients` | Entreprise cliente | Référencée par `utilisateurs.client_id`, `requetes.client_id` |
| `utilisateurs` | Tous les comptes (super admin, admins/utilisateurs client, techniciens) | `client_id` → `clients.id` (nullable pour super admin / technicien interne selon règle métier) |
| `requetes` | Ticket / demande d’intervention | `client_id` → `clients`, `user_id` → `utilisateurs` (auteur), `technicien_id` → `utilisateurs` (technicien assigné, optionnel) |
| `medias` | Fichiers liés à une requête | `requete_id` → `requetes` |
| `planifications` | Créneaux / rendez-vous | `requete_id` → `requetes`, `technicien_id` → `utilisateurs` |
| `interventions` | Compte rendu technique sur le terrain | `requete_id` → `requetes`, `technicien_id` → `utilisateurs` |
| `validations` | Étapes de validation client / technicien | `requete_id` → `requetes` |
| `recus` | Métadonnée du PDF généré | `requete_id` → `requetes` |
| `logs` | Journalisation | `user_id` → `utilisateurs` (optionnel) |

**Laravel (tables système, hors métier) :** le dump inclut aussi `migrations`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` — typique d’un projet Laravel déjà initialisé (files d’attente, cache DB optionnel).

**Note technique :** le script définit des index sur les clés étrangères logiques mais **pas de contraintes `FOREIGN KEY`**. La phase 1 doit encore **verrouiller l’intégrité référentielle** si ce n’est pas fait.

**Techniciens :** pas de table `techniciens` ; les techniciens sont des lignes dans **`utilisateurs`** avec `role = 'technicien'`.

**Côté code Laravel :** le modèle d’authentification peut s’appeler `User` mais doit pointer sur la table **`utilisateurs`** (`protected $table = 'utilisateurs';` et configuration `config/auth.php` si besoin).

---

## Phase 0 — Cadrage, environnement et socle projet

**Objectif :** disposer d’un projet Laravel prêt à consommer la base `intervention_bd`, avec conventions de déploiement et stockage fichiers.

**À faire (précis) :**
- Initialiser l’application web **responsive** (desktop + mobile) — conforme CDC §6.
- Vérifier la **compatibilité PHP** avec Laravel cible (le dump mentionne PHP 8.3 ; WAMP doit fournir une version supportée).
- Configurer la connexion MySQL vers **`intervention_bd`** (charset **utf8mb4** / collation alignés sur le script).
- Aligner **fuseau horaire / locale** applicative (`config/app.php`) avec l’usage métier (horaires d’intervention).
- Définir l’arborescence de **stockage hors base** pour médias et PDF (chemins cohérents avec `medias.chemin` et `recus.chemin_pdf`) — CDC §6 ; commande `php artisan storage:link` si fichiers publics contrôlés.
- **Variables d’environnement** : `.env` (`DB_*`, `APP_URL`, `APP_KEY`), **mail** (reçus / notifications — CDC §4.5, §4.8), **queue** si jobs asynchrones (`jobs` / `failed_jobs` sont déjà dans la base).
- Prévoir **HTTPS** en production (CDC §5).
- **Authentification sur `utilisateurs` :** configurer le modèle utilisé par Laravel (`Authenticatable`) pour la table **`utilisateurs`** (pas `users`).
- (Pratique) **Seeder** ou insertion manuelle d’un premier compte `super_admin` pour tester la phase 2.

**Tables :** aucune modification obligatoire côté métier ; le dump prévoit déjà les tables Laravel `cache`, `jobs`, `job_batches`, `failed_jobs`, `migrations`.

### Vérification phase 0 par rapport à `intervention_bd.sql` (état actuel)

| Attendu phase 0 | Dans le dump |
|-----------------|--------------|
| Socle Laravel + MySQL | Présence de `migrations`, `jobs`, `cache`, etc. → **cohérent avec un projet Laravel déjà passé sur cette base** |
| Schéma métier | Tables `clients`, `utilisateurs`, `requetes`, … **présentes** |
| Stockage fichiers / `.env` / UI responsive | **Hors SQL** — à valider dans le code et le déploiement |

**Conclusion :** la **base** reflète bien un socle Laravel + schéma métier ; la phase 0 n’est **pas** « terminée » tant que l’app pointe vers cette BDD, que l’auth cible **`utilisateurs`**, et que le stockage + `.env` sont en place.

---

## Phase 1 — Schéma BDD, intégrité et migrations Laravel

**Objectif :** base exploitable en production avec règles claires (types cohérents, FK, évolutions tracées).

**Réalisé (couronnement phase 1) :**
- **Migrations Laravel** alignées sur `database/migrations/` : ordre d’exécution corrigé (`requetes` avant `interventions` / `planifications` / `recus`).
- Table des reçus unifiée sous le nom **`recus`** (plus de migration vers une table `recuses`).
- Références **`utilisateurs`** partout (plus de `users` inexistant) : `requetes`, `interventions`, `planifications`, `logs`.
- **`utilisateurs.client_id`** en `foreignId` nullable → `clients.id` avec **`ON DELETE SET NULL`**.
- Colonne **`remember_token`** sur `utilisateurs` (session Laravel / « se souvenir de moi »).
- **Contraintes FK** sur toutes les relations métier (cascade sur filles de `requetes`, `RESTRICT` / `SET NULL` selon colonnes).
- Index composite **`requetes(client_id, date_creation)`** pour le reporting.
- Fichier **`intervention_bd.sql`** régénéré : même schéma + FK + enregistrements `migrations` à jour (11 migrations, batch 1).
- **`config/auth.php`** pointe vers **`App\Models\Utilisateurs`** ; modèle **`Utilisateurs`** étend **`Authenticatable`** (pré-requis propre à la phase 2).

**Reste hors phase 1 (fonctionnel / doc) :**
- **Numéro de ticket** affiché (CDC §4.3) : dérivé de `requetes.id` ou colonne dédiée — à trancher en phase 5.
- **Machine à états** des statuts de requête : à documenter dans le code (transitions autorisées).

**Ordre des migrations appliqué :** `clients` → **`utilisateurs`** → **`logs`** → **`requetes`** → **`recus`** → **`interventions`** → **`planifications`** → **`validations`** → **`medias`**.

**Tables :** toutes les tables métier + **`logs`** ; tables système Laravel (`cache`, `jobs`, …).

### Vérification phase 1 par rapport à `intervention_bd.sql` (état actuel)

| Attendu phase 1 | État |
|-----------------|------|
| Schéma via migrations | **OK** — 11 migrations, ordre cohérent (`php artisan migrate:fresh` validé) |
| Intégrité référentielle (FK) | **OK** — `FOREIGN KEY` dans le dump SQL et créées par les migrations |
| Types `utilisateurs.client_id` / `clients.id` | **OK** — `bigint UNSIGNED` aligné |
| Numéro de ticket / machine à états | **À faire** plus tard (voir ci-dessus) |

**Conclusion :** la phase 1 schéma / intégrité est **bouclée** ; enchaîner avec la phase 2 (auth sur `utilisateurs`).

---

## Phase 2 — Authentification, rôles et permissions (CDC §3.1–3.3, §4.2, §5)

**Objectif :** connexion sécurisée et cloisonnement des accès.

**Implémenté dans le projet :**
- **Connexion / déconnexion** : `App\Http\Controllers\Auth\LoginController`, routes `login`, `login.store`, `logout` dans `routes/web.php`.
- **Mot de passe** : cast `hashed` sur `Utilisateurs` ; tentative avec `Auth::attemptWhen(...)` pour n’accepter que **`statut = actif`**.
- **Middleware `compte.actif`** : si un admin désactive le compte pendant une session, l’utilisateur est expulsé au chargement suivant (`EnsureCompteActif`).
- **Middleware `role:`** : ex. `->middleware('role:super_admin')` ou `role:super_admin,client_admin` (OU logique) — classe `EnsureRole`.
- **Enum `RoleUtilisateur`** : miroir des valeurs BDD + méthode `label()` pour l’UI.
- **Policies** : `ClientPolicy`, `RequetesPolicy` + trait `AuthorizesRequests` sur `Controller` — utiliser `$this->authorize(...)` dans les futurs contrôleurs.
- **Vues** : `resources/views/auth/login.blade.php`, `dashboard.blade.php`, layout `layouts/app.blade.php`.
- **Locale** : défaut `fr` dans `config/app.php`, messages `lang/fr/auth.php`.
- **Seed** : `DatabaseSeeder` crée `admin@example.com` / `password` (super admin) — **à changer en production**.
- **Tests** : `tests/Feature/Auth/LoginTest.php` (connexion OK, compte `inactif` refusé).
- **Configuration** : alias middleware + redirection invité / utilisateur dans `bootstrap/app.php`.

**À brancher plus tard :** CRUD détaillés `RequetesController` (phase 5) avec `$this->authorize` sur chaque action.

**Tables :** **`utilisateurs`** (cœur), lecture **`clients`** pour filtrage multi-tenant.

---

## Phase 3 — Module clients (CDC §4.1)

**Objectif :** CRUD entreprises clientes et activation.

**Implémenté :**
- **CRUD** : `ClientController` + `routes/web.php` → `Route::resource('clients', ...)` ; validation `StoreClientRequest` / `UpdateClientRequest`.
- **Champs** : `nom_entreprise`, `email`, `telephone`, `adresse`, `statut` (modifiable **uniquement par le super admin** ; l’admin client peut mettre à jour le reste sans toucher au statut).
- **Historique / synthèse** : page `clients.show` avec compteurs requêtes & utilisateurs + **25 dernières requêtes** ; liste paginée `clients.index` (super admin) avec compteurs.
- **Client inactif** : `Client::peutRecevoirNouvellesRequetes()` ; refus côté `RequetesController@store` (JSON) si client inactif.
- **Logo YCS** : fichier source `logo-ycs.png` à la racine du projet ; **copie servie au navigateur** : `public/images/logo-ycs.png` (`asset('images/logo-ycs.png')`) — en-tête `layouts/app.blade.php` et page `auth/login.blade.php`.
- **Navigation** : liens « Clients », « Mon entreprise », charte rouge / noir **YAOCOM'S GROUPE**.
- **Tests** : `tests/Feature/ClientModuleTest.php`.

**Tables :** **`clients`**, jointures **`requetes`**, **`utilisateurs`** (comptes rattachés au client).

---

## Phase 4 — Module utilisateurs clients (CDC §4.2, lien avec §4.1)

**Objectif :** admins client et utilisateurs simples rattachés à une entreprise.

**Implémenté :**
- **CRUD** : `UtilisateursController` + `Route::resource('utilisateurs', …)` avec segment d’URL `/{utilisateur}` et **binding** explicite dans `AppServiceProvider` (modèle `Utilisateurs`).
- **Policy** : `UtilisateursPolicy` — liste (`viewAny`) : super admin + admin client ; édition / suppression : règles multi-tenant (pas de suppression de soi-même ; admin client ne touche pas aux techniciens / super admins).
- **Formulaires** : `StoreUtilisateurRequest`, `UpdateUtilisateurRequest` (rôles autorisés selon l’acteur ; `client_id` forcé pour l’admin client ; règles super admin / technicien sans `client_id`).
- **Profil** : un **`client_user`** qui modifie **son** compte utilise `_form_profil` (nom, e-mail, mot de passe optionnel) sans changer rôle / statut.
- **Vues** : `resources/views/utilisateurs/*` ; navigation « Utilisateurs » dans le layout + tableau de bord ; lien depuis la fiche **client** (`?client_id=` pour filtrer côté super admin).
- **Tests** : `tests/Feature/UtilisateursModuleTest.php`.

**Tables :** **`utilisateurs`**, **`clients`**.

---

## Phase 5 — Module requêtes d’intervention (CDC §4.3)

**Objectif :** cycle de vie du ticket depuis la création jusqu’aux statuts avancés.

**À faire :**
- Formulaire création : `titre`, `description`, `urgence` (`faible`, `moyenne`, `elevee`), rattachement automatique `client_id` (depuis l’utilisateur connecté), `user_id` auteur, `date_creation`.
- Génération / affichage du **numéro de ticket** (voir phase 1).
- **Attribution technicien** : renseigner `requetes.technicien_id` (ligne **`utilisateurs`** avec rôle technicien) + mise à jour `statut` (ex. `en_attente` → `planifiee` selon règles).
- Liste et filtre par statut, urgence, client, dates (`date_planification`, `date_intervention`, `date_fin`).

**Tables :** **`requetes`**, jointures **`clients`**, **`utilisateurs`** (auteur + technicien).

---

## Phase 6 — Module médias (CDC §4.4, §6 stockage fichiers)

**Objectif :** pièces jointes sécurisées et traçables.

**À faire :**
- Upload avec limite de taille, types autorisés, nommage et dossier par client/requête (hors BDD).
- Enregistrer une ligne **`medias`** : `requete_id`, `type` (`image` / `video`), `chemin`, `taille`.
- Optionnel CDC : compression images côté serveur avant enregistrement.
- Affichage des médias sur la fiche requête ; contrôle d’accès (même périmètre que la requête).

**Tables :** **`medias`**, **`requetes`**.

---

## Phase 7 — Planification et notifications (CDC §4.5)

**Objectif :** planifier l’intervention et informer le client.

**À faire :**
- Création d’une **`planifications`** : `requete_id`, `technicien_id`, `date_intervention`, `message`, `statut` (`planifiee`, `confirmee`, `annulee`).
- Mettre à jour **`requetes`** : `date_planification`, éventuellement `statut = 'planifiee'`, cohérence avec `technicien_id`.
- **Historique des planifications** : liste ordonnée par `created_at` pour une même requête (plusieurs lignes possibles si reports).
- **Notification client** : email (ou autre canal) au moment de la planification — hors BDD, mais peut être journalisé dans **`logs`**.

**Tables :** **`planifications`**, **`requetes`**, **`utilisateurs`**.

---

## Phase 8 — Validations multi-acteurs (CDC §4.6)

**Objectif :** tracer les validations client et technicien.

**À faire :**
- Une ligne **`validations`** par requête concernée (ou mise à jour de la même ligne) avec :
  - `client_arrivee` : validation « arrivée du technicien » côté client.
  - `client_fin` : validation « fin d’intervention » côté client.
  - `technicien_fin` : confirmation finale technicien.
- Le CDC mentionne aussi **« confirmation intervention en cours »** : soit un champ supplémentaire à ajouter en migration (ex. `client_intervention_en_cours`), soit une convention sur `requetes.statut` + horodatage — à clarifier en conception pour ne pas perdre l’exigence.
- Interfaces distinctes selon rôle (client vs technicien).

**Tables :** **`validations`**, **`requetes`**, **`utilisateurs`**.

---

## Phase 9 — Intervention technique sur le terrain (CDC §4.7)

**Objectif :** compte rendu après ou pendant l’intervention.

**À faire :**
- Création / édition **`interventions`** : `rapport` (actions effectuées), `pieces_utilisees`, `heure_debut`, `heure_fin`, `statut` (`en_cours`, `terminee`).
- Lier à la **requête** et au **technicien** (`requete_id`, `technicien_id`).
- Cohérence avec `requetes.statut` (passage `en_cours` → `terminee` / `cloturee` selon règles métier).

**Tables :** **`interventions`**, **`requetes`**, **`utilisateurs`**.

---

## Phase 10 — Reçu PDF et envoi (CDC §4.8)

**Objectif :** document officiel téléchargeable et archivable.

**À faire :**
- Génération PDF (bibliothèque Laravel type DomPDF / Snappy) : synthèse requête, client, technicien, dates, rapport, pièces, signatures « logiques » (cases cochées / horodatages issus de **`validations`**).
- Stockage fichier ; enregistrer **`recus`** avec `requete_id` et `chemin_pdf`.
- Actions : téléchargement, impression, envoi email (lien ou pièce jointe).

**Tables :** **`recus`**, jointures **`requetes`**, **`clients`**, **`utilisateurs`**, **`interventions`**, **`validations`**.

---

## Phase 11 — Archivage, historique et traçabilité (CDC §4.9)

**Objectif :** consultation durable et filtres multiples.

**À faire :**
- Les données restent en base (pas de suppression métier des interventions terminées sauf politique d’archivage explicite).
- Écrans d’historique avec filtres :
  - par **client** : `requetes.client_id` ;
  - par **technicien** : `requetes.technicien_id` / `interventions.technicien_id` ;
  - par **date** : `date_creation`, `date_intervention`, `heure_debut` / `heure_fin` ;
  - par **type de problème** : si seulement `titre`/`description` existent, prévoir recherche plein texte ou champ catégorie futur (écart possible avec le CDC si aucune table `types_panne` n’est ajoutée).

**Tables :** **`requetes`**, **`interventions`**, **`planifications`**, **`validations`**, **`recus`**, **`medias`**.

---

## Phase 12 — Reporting et statistiques (CDC §4.10)

**Objectif :** tableaux de bord et indicateurs.

**À faire :**
- Requêtes agrégées SQL / Eloquent :
  - nombre d’interventions par période (`interventions`, `requetes.dates`) ;
  - performance par technicien (volume, délais) ;
  - temps moyen de résolution (écart `date_creation` → `date_fin` ou `heure_fin`) ;
  - clients les plus actifs (count par `client_id`) ;
  - pannes fréquentes (analyse `titre` / mots-clés ou future table de catégories).

**Tables :** principalement **`requetes`**, **`interventions`**, **`clients`**, **`utilisateurs`**.

---

## Phase 13 — Sécurité, logs et durcissement (CDC §5 + §6)

**Objectif :** traçabilité des actions et protection des fichiers.

**À faire :**
- Écrire dans **`logs`** : `user_id`, `action`, `description`, `ip_address`, `created_at` pour actions sensibles (connexion, changement statut, upload, génération PDF, validation).
- Contrôle d’accès aux **URLs de fichiers** (pas de lien direct public non authentifié si données confidentielles).
- Validation stricte des uploads (MIME, taille, renommage).

**Tables :** **`logs`**, **`utilisateurs`**.

---

## Synthèse : modules CDC ↔ phases proposées

| Module CDC | Phase(s) | Tables dominantes |
|------------|----------|-------------------|
| 4.1 Clients | 3 | `clients`, `requetes` |
| 4.2 Utilisateurs | 2, 4 | `utilisateurs`, `clients` |
| 4.3 Requêtes | 5 | `requetes`, `utilisateurs`, `clients` |
| 4.4 Médias | 6 | `medias`, `requetes` |
| 4.5 Planification | 7 | `planifications`, `requetes`, `utilisateurs` |
| 4.6 Validation | 8 | `validations`, `requetes` |
| 4.7 Intervention technique | 9 | `interventions`, `requetes`, `utilisateurs` |
| 4.8 Reçu PDF | 10 | `recus`, + tables liées à la requête |
| 4.9 Archivage / historique | 11 | `requetes`, `interventions`, … |
| 4.10 Reporting | 12 | `requetes`, `interventions`, `clients` |
| 5 Sécurité | 2, 13 | `utilisateurs`, `logs` |

---

## Écarts à anticiper (hors script SQL actuel)

1. **Numéro de ticket** affichable / unique : à ajouter ou à dériver de `id`.
2. **« Confirmation intervention en cours »** : pas de colonne dédiée dans `validations` — extension du schéma ou usage combiné statuts + dates.
3. **Types / catégories de pannes** : utile pour le reporting « types fréquents » ; absent du SQL actuel.
4. **Contraintes FK** : absentes du dump ; à ajouter en phase 1 (voir tableau de vérification ci-dessus).
5. **Table `utilisateurs`** : remplace la convention Laravel `users` ; le code doit utiliser ce nom de table explicitement.

---

*Document généré pour aligner le développement sur `cahier_charge.txt` et `intervention_bd.sql`.*
