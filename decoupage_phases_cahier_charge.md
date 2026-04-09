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

**Implémenté :**
- **Stockage** : disque Laravel **`medias_interventions`** → `storage/app/medias_interventions` (privé, pas d’URL publique) ; chemins relatifs `{client_id}/{requete_id}/{uuid}.ext`.
- **Config** : `config/medias.php` — taille max (`MEDIA_MAX_UPLOAD_KO`), MIME autorisés, largeur max / qualité JPEG pour compression.
- **Contrôleur** : `MediasController` — `store` (upload + ligne `medias`), `fichier` (réponse `file()` avec `authorize('view', $requete)`), `destroy` (fichier + ligne, `authorize('update', $requete)`). Compression optionnelle JPEG/PNG/WebP via **GD** après enregistrement.
- **Validation** : `StoreMediaRequest` (taille + MIME depuis la config ; `authorize` via `can('update', $requete)`).
- **UI** : fiche requête `resources/views/requetes/show.blade.php` — liste, miniatures / vidéo inline, formulaire d’upload, suppression.
- **Routes** : `requetes.medias.store`, `requetes.medias.fichier`, `requetes.medias.destroy` dans `routes/web.php` ; bindings `{requete}` / `{media}` dans `AppServiceProvider`.
- **Tests** : `tests/Feature/MediaPhase6Test.php`.

**Note :** pas de `storage:link` nécessaire tant que la diffusion passe par la route authentifiée `fichier`.

**Tables :** **`medias`**, **`requetes`**.

---

## Phase 7 — Planification et notifications (CDC §4.5)

**Objectif :** planifier l’intervention et informer le client.

**Implémenté :**
- **Création** : `POST requetes/{requete}/planifications` — `RequetePlanificationController@store` + `StorePlanificationRequest` ; réservé au **super admin** (`assignerTechnicien`). Crée une ligne **`planifications`** (`statut = planifiee`), met à jour **`requetes`** : `technicien_id`, `date_planification`, `date_intervention`, `statut = planifiee`.
- **Historique** : relation `Requetes::planifications()` (tri `created_at` décroissant) ; bloc sur `requetes/show.blade.php`.
- **Statuts** : `PATCH requetes/{requete}/planifications/{planification}` — `UpdatePlanificationRequest` : **confirmee** (client même entreprise, `confirmerPlanification`) ; **annulee** (super admin).
- **E-mail** : `App\Mail\PlanificationNotifiee` → vue `emails/planification-notifiee` ; destinataire = e-mail **client** ou premier **admin client** actif si l’entreprise n’a pas d’e-mail.
- **Journal** : table **`logs`** (`action` `planification_creee`, `planification_statut_confirmee`, `planification_statut_annulee`) ; modèle **`Log`** aligné sur les colonnes BDD (`ip_address`, etc.).
- **Policy** : `RequetesPolicy::confirmerPlanification` pour les rôles client sur la même entreprise.
- **Tests** : `tests/Feature/PlanificationPhase7Test.php`.

**Tables :** **`planifications`**, **`requetes`**, **`utilisateurs`**.

---

## Phase 8 — Validations multi-acteurs (CDC §4.6)

**Objectif :** tracer les validations client et technicien.

**Implémenté :**
- **Schéma** : migration `2026_04_08_120000_phase8_validations_horodatages` — une ligne **`validations`** par requête (`requete_id` **unique**), horodatages nullable : `client_arrivee_at`, `client_intervention_en_cours_at`, `client_fin_at`, `technicien_fin_at` (remplace les anciens booléens + `date_validation`).
- **API web** : `POST requetes/{requete}/validations` — `RequeteValidationController@store` + `StoreRequeteValidationRequest` (`etape` : `client_arrivee` | `client_intervention_en_cours` | `client_fin` | `technicien_fin`). Création paresseuse de la ligne ; idempotence si l’étape est déjà horodatée (`validation_deja_enregistree`).
- **Policy** : `validerArriveeClient`, `validerInterventionEnCoursClient`, `validerFinInterventionClient` (rôles client, même entreprise, **technicien assigné**) ; `validerFinTechnicien` (technicien assigné à la requête). Pas d’actions validation pour le super admin sur ce flux (lecture seule sur la fiche).
- **Journal** : `logs.action` = `validation_client_arrivee`, `validation_client_intervention_en_cours`, `validation_client_fin`, `validation_technicien_fin`.
- **UI** : bloc « Validations » sur `requetes/show.blade.php` — récapitulatif + boutons selon `@can` (client vs technicien).
- **Tests** : `tests/Feature/ValidationsPhase8Test.php`.

**Tables :** **`validations`**, **`requetes`**, **`utilisateurs`**.

---

## Phase 9 — Intervention technique sur le terrain (CDC §4.7)

**Objectif :** compte rendu après ou pendant l’intervention.

**Implémenté :**
- **Schéma** : table **`interventions`** (déjà en place) — `rapport`, `pieces_utilisees`, `heure_debut`, `heure_fin`, `statut` (`en_cours`, `terminee`), `requete_id`, `technicien_id` (aligné sur le technicien assigné à la requête).
- **API web** : `POST requetes/{requete}/intervention` (`store`) — première fiche uniquement ; `PATCH requetes/{requete}/intervention` (`update`). `SaveInterventionRequest` : si `statut = terminee`, **`heure_fin`** obligatoire ; cohérence début / fin ; une fois **`terminee`**, le statut ne peut plus repasser en `en_cours`.
- **Policy** : `gererInterventionTerrain` = technicien assigné à la requête (même règle que fin de validation technicien).
- **Requête** : passage **`requetes.statut`** à `en_cours` lorsque l’intervention est `en_cours` (sauf si la requête est déjà `terminee` / `cloturee`) ; à **`terminee`**, la requête passe en `terminee` et **`date_fin`** = `heure_fin` ou maintenant. Le statut **`cloturee`** reste pour une évolution ultérieure (pas géré ici).
- **Journal** : `intervention_creee`, `intervention_mise_a_jour`, `intervention_terminee` (lors du passage à terminée, création ou mise à jour).
- **UI** : bloc sur `requetes/show.blade.php` (résumé + formulaire technicien).
- **Tests** : `tests/Feature/InterventionsPhase9Test.php`.

**Tables :** **`interventions`**, **`requetes`**, **`utilisateurs`**.

---

## Phase 10 — Reçu PDF et envoi (CDC §4.8)

**Objectif :** document officiel téléchargeable et archivable.

**Implémenté :**
- **PDF** : package **`barryvdh/laravel-dompdf`** ; vue `pdf/recu-intervention` (requête, client, technicien, intervention, validations avec horodatages).
- **Stockage** : disque **`recus_interventions`** (`storage/app/recus_interventions`), chemin type `requetes/{id}/recu.pdf` ; table **`recus`** avec `requete_id` **unique** + `chemin_pdf` (`updateOrCreate`).
- **Routes** : `POST requetes/{requete}/recu/pdf` (générer / régénérer), `GET …/recu/pdf` (téléchargement), `POST …/recu/envoyer` (e-mail avec **pièce jointe**). Config `config/recus.php` (`disk`).
- **Policy** : `genererRecuPdf` si `view` + intervention **`terminee`** ; `telechargerRecuPdf` / `envoyerRecuPdfEmail` si reçu enregistré avec fichier. **Mailable** `RecuInterventionEnvoye` ; destinataire = e-mail entreprise ou premier admin client actif (comme planification).
- **Journal** : `recu_pdf_genere`, `recu_pdf_envoye`.
- **UI** : bloc « Reçu PDF » sur `requetes/show.blade.php`.
- **Tests** : `tests/Feature/RecusPhase10Test.php`.

**Tables :** **`recus`**, jointures **`requetes`**, **`clients`**, **`utilisateurs`**, **`interventions`**, **`validations`**.

---

## Phase 11 — Archivage, historique et traçabilité (CDC §4.9)

**Objectif :** consultation durable et filtres multiples.

**Implémenté :**
- **Pas de suppression métier** : rappel UI sur l’écran ; aucune action de purge ajoutée.
- **Écran** : `GET historique/requetes` — `HistoriqueRequeteController@index`, nom de route `historique.requetes`, lien **Historique** dans `layouts/app.blade.php`.
- **Périmètre** : même base que la liste requêtes — `Requetes::visiblesPour($user)` + `viewAny` sur `Requetes`.
- **Filtres** :
  - **Client** (`requetes.client_id`) : super admin uniquement ;
  - **Technicien** (`requetes.technicien_id`) : super admin (tous les techniciens actifs) ; rôles client — liste dérivée des techniciens déjà présents sur les tickets de l’entreprise (équivalent périmètre `interventions.technicien_id` pour les fiches liées) ;
  - **Période** : `date_debut` / `date_fin` + axe `date_creation` | `date_intervention` | `intervention_debut` (`interventions.heure_debut`) | `intervention_fin` (`interventions.heure_fin`) ;
  - **Problème** : recherche **LIKE** sur `titre` et `description` (pas de table `types_panne`).
- **Présentation** : tableau enrichi (dates, technicien, plage intervention), pagination simple, lien vers la fiche requête.
- **Tests** : `tests/Feature/HistoriquePhase11Test.php`.

**Tables :** **`requetes`**, **`interventions`**, **`planifications`**, **`validations`**, **`recus`**, **`medias`**.

---

## Phase 12 — Reporting et statistiques (CDC §4.10)

**Objectif :** tableaux de bord et indicateurs.

**Implémenté :**
- **Route** : `GET /reporting` — `ReportingController@index` (`reporting.index`), filtre `date_debut` / `date_fin` (défaut : **30 derniers jours** jusqu’à aujourd’hui). Lien **Reporting** dans la barre de navigation et le tableau de bord.
- **Autorisation** : `viewAny` sur `Requetes` ; **périmètre** des agrégats = `Requetes::visiblesPour($user)` (comme listes / historique).
- **Service** `RequeteReportingService::resume()` :
  - **Requêtes créées** sur la période (`date_creation`) ;
  - **Interventions terminées** dont `heure_fin` est dans la période ;
  - **Par technicien** : volume + **délai moyen (h)** entre `requetes.date_creation` et `interventions.heure_fin` ;
  - **Temps moyen de résolution** : moyenne des écarts `date_creation` → `date_fin` pour tickets avec `date_fin` dans la période ;
  - **Clients actifs** : top 10 par nombre de tickets créés (jointure `clients`) ;
  - **Titres fréquents** : top 10 libellés de `titre` identiques (pas de mots-clés ni table `types_panne`).
- **UI** : `resources/views/reporting/index.blade.php` (cartes + tableaux).
- **Tests** : `tests/Feature/ReportingPhase12Test.php`.

**Tables :** principalement **`requetes`**, **`interventions`**, **`clients`**, **`utilisateurs`**.

---

## Phase 13 — Sécurité, logs et durcissement (CDC §5 + §6)

**Objectif :** traçabilité des actions et protection des fichiers.

**Dernière phase numérotée** du découpage (les modules CDC hors périmètre « phases » restent décrits dans la synthèse et les écarts).

**Implémenté :**
- **Journalisation centralisée** : `App\Support\Journalisation` (`trace` / `traceSansUtilisateur`) → table **`logs`** (`user_id`, `action`, `description`, `ip_address`, timestamps).
- **Événements tracés** (non exhaustif) : `connexion_reussie`, `connexion_echouee`, `deconnexion`, `client_statut_modifie`, `utilisateur_statut_modifie`, `media_upload`, `media_supprime`, `requete_statut_modifie` (synchro intervention), actions existantes planification / validation / intervention / PDF (`recu_pdf_telecharge` en plus de génération / envoi).
- **Fichiers** : médias et PDF reçus servis **uniquement** par routes `auth` + `compte.actif` + policies (`requetes.medias.fichier`, `requetes.recu.pdf.download`) ; disques **hors** `public` direct.
- **Uploads** : `config/medias.php` — liste d’**extensions**, tableau **MIME attendus par extension**, contrôle **nom de fichier** (pas de `..` / séparateurs) ; stockage **UUID** + extension (inchangé).
- **Tests** : `tests/Feature/SecuritePhase13Test.php`.

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
2. **« Confirmation intervention en cours »** : couverte en phase 8 par `validations.client_intervention_en_cours_at`.
3. **Types / catégories de pannes** : utile pour le reporting « types fréquents » ; absent du SQL actuel.
4. **Contraintes FK** : absentes du dump ; à ajouter en phase 1 (voir tableau de vérification ci-dessus).
5. **Table `utilisateurs`** : remplace la convention Laravel `users` ; le code doit utiliser ce nom de table explicitement.

---

*Document généré pour aligner le développement sur `cahier_charge.txt` et `intervention_bd.sql`.*
