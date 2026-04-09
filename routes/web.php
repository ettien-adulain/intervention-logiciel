<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoriqueRequeteController;
use App\Http\Controllers\MediasController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\RequeteController;
use App\Http\Controllers\RequeteInterventionController;
use App\Http\Controllers\RequetePlanificationController;
use App\Http\Controllers\RequeteRecuController;
use App\Http\Controllers\RequeteValidationController;
use App\Http\Controllers\UtilisateursController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
| Page d’accueil : application interne (pas de vitrine). Invité → connexion ; connecté → tableau de bord.
*/
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Authentification (phase 2 — CDC)
|--------------------------------------------------------------------------
| - Pas d’inscription publique : les comptes sont créés par le super admin
|   ou l’admin client (phases 3–4).
| - `guest` : évite d’afficher le formulaire de connexion si déjà connecté.
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Espace authentifié
|--------------------------------------------------------------------------
| `compte.actif` : coupe la session si statut passé à `inactif` en cours de route.
*/
Route::middleware(['auth', 'compte.actif'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/reporting', [ReportingController::class, 'index'])->name('reporting.index');

    /*
     * Phase 3 — Clients (CDC §4.1) : droits via `ClientPolicy` + `$this->authorize` dans le contrôleur.
     * Pas de middleware `role:` global ici : un admin client peut éditer sa propre fiche.
     */
    Route::resource('clients', ClientController::class);

    /*
     * Phase 4 — Utilisateurs (CDC §4.2) : paramètre d’URL `utilisateur` → binding dans AppServiceProvider.
     */
    Route::resource('utilisateurs', UtilisateursController::class)
        ->parameters(['utilisateurs' => 'utilisateur']);

    /*
     * Requêtes (web) + médias phase 6 — accès via RequetesPolicy.
     * Fichiers (médias, PDF reçus) : pas d’URL publique directe ; diffusion uniquement ici (phase 13).
     */
    Route::get('historique/requetes', [HistoriqueRequeteController::class, 'index'])
        ->name('historique.requetes');

    Route::get('requetes/create', [RequeteController::class, 'create'])->name('requetes.create');
    Route::post('requetes', [RequeteController::class, 'store'])->name('requetes.store');
    Route::get('requetes', [RequeteController::class, 'index'])->name('requetes.index');
    Route::post('requetes/{requete}/medias', [MediasController::class, 'store'])->name('requetes.medias.store');
    Route::get('requetes/{requete}/medias/{media}/fichier', [MediasController::class, 'fichier'])->name('requetes.medias.fichier');
    Route::delete('requetes/{requete}/medias/{media}', [MediasController::class, 'destroy'])->name('requetes.medias.destroy');

    Route::post('requetes/{requete}/planifications', [RequetePlanificationController::class, 'store'])
        ->name('requetes.planifications.store');
    Route::patch('requetes/{requete}/planifications/{planification}', [RequetePlanificationController::class, 'update'])
        ->name('requetes.planifications.update');

    Route::post('requetes/{requete}/validations', [RequeteValidationController::class, 'store'])
        ->name('requetes.validations.store');

    Route::post('requetes/{requete}/intervention', [RequeteInterventionController::class, 'store'])
        ->name('requetes.intervention.store');
    Route::patch('requetes/{requete}/intervention', [RequeteInterventionController::class, 'update'])
        ->name('requetes.intervention.update');

    Route::post('requetes/{requete}/recu/pdf', [RequeteRecuController::class, 'store'])
        ->name('requetes.recu.pdf.store');
    Route::get('requetes/{requete}/recu/pdf', [RequeteRecuController::class, 'download'])
        ->name('requetes.recu.pdf.download');

    Route::get('requetes/{requete}', [RequeteController::class, 'show'])->name('requetes.show');
});
