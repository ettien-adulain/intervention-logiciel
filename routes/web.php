<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UtilisateursController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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
});
