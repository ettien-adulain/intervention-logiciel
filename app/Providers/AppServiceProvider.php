<?php

namespace App\Providers;

use App\Models\Utilisateurs;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Les policies `ClientPolicy` et `RequetesPolicy` sont découvertes automatiquement
     * par Laravel (convention de nommage). Ajoutez ici d’autres `Gate::policy(...)`
     * si vous introduisez des modèles sans suffixe Policy standard.
     */
    public function boot(): void
    {
        // `simplePaginate()` — navigation Précédent / Suivant sans dépendre du thème Tailwind par défaut.
        Paginator::defaultSimpleView('pagination.simple');

        // Modèle `Utilisateurs` : le segment de route s’appelle `utilisateur` (singulier).
        Route::bind('utilisateur', fn (string $value) => Utilisateurs::query()->findOrFail($value));
    }
}
