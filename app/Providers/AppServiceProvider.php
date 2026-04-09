<?php

namespace App\Providers;

use App\Models\Medias;
use App\Models\Planification;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use App\Support\RequeteActionsEnAttente;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.app', function ($view) {
            if (! auth()->check()) {
                $view->with('requetesActionsEnAttenteCount', 0);

                return;
            }
            $view->with(
                'requetesActionsEnAttenteCount',
                RequeteActionsEnAttente::countPour(auth()->user())
            );
        });

        // `simplePaginate()` — navigation Précédent / Suivant sans dépendre du thème Tailwind par défaut.
        Paginator::defaultSimpleView('pagination.simple');

        // Modèle `Utilisateurs` : le segment de route s’appelle `utilisateur` (singulier).
        Route::bind('utilisateur', fn (string $value) => Utilisateurs::query()->findOrFail($value));

        Route::bind('requete', fn (string $value) => Requetes::query()->findOrFail($value));
        Route::bind('media', fn (string $value) => Medias::query()->findOrFail($value));

        Route::bind('planification', function (string $value, \Illuminate\Routing\Route $route) {
            $requete = $route->parameter('requete');
            if (! $requete instanceof Requetes) {
                abort(404);
            }

            return Planification::query()
                ->where('requete_id', $requete->id)
                ->whereKey($value)
                ->firstOrFail();
        });
    }
}
