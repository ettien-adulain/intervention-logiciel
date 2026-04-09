<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Phase 11 — CDC §4.9 : historique des requêtes avec filtres (consultation, pas de suppression métier).
 */
class HistoriqueRequeteController extends Controller
{
    private const AXES_TEMPORELS = [
        'date_creation',
        'date_intervention',
        'intervention_debut',
        'intervention_fin',
    ];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Requetes::class);

        $user = $request->user();

        $query = Requetes::query()
            ->visiblesPour($user)
            ->with(['client', 'user', 'technicien', 'intervention']);

        if ($user->estSuperAdmin() && $request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        $this->appliquerFiltreTechnicien($query, $request, $user);
        $this->appliquerRechercheTexte($query, $request);
        $this->appliquerFiltreDates($query, $request);

        $requetes = $query->orderByDesc('id')->simplePaginate(25)->withQueryString();

        $clientsFiltre = $user->estSuperAdmin()
            ? Client::query()->orderBy('nom_entreprise')->get()
            : collect();

        $techniciensFiltre = $this->techniciensPourFiltre($user);

        return view('historique.requetes', [
            'requetes' => $requetes,
            'clientsFiltre' => $clientsFiltre,
            'techniciensFiltre' => $techniciensFiltre,
        ]);
    }

    private function appliquerFiltreTechnicien(Builder $query, Request $request, Utilisateurs $user): void
    {
        if (! $request->filled('technicien_id')) {
            return;
        }

        $tid = $request->integer('technicien_id');

        if ($user->estSuperAdmin()) {
            $query->where('technicien_id', $tid);

            return;
        }

        if (in_array($user->role, [RoleUtilisateur::ClientAdmin, RoleUtilisateur::ClientUser], true)
            && $user->client_id !== null) {
            $autorise = Requetes::query()
                ->where('client_id', $user->client_id)
                ->where('technicien_id', $tid)
                ->exists();
            if ($autorise) {
                $query->where('technicien_id', $tid);
            }
        }
    }

    private function techniciensPourFiltre(Utilisateurs $user): Collection
    {
        if ($user->estSuperAdmin()) {
            return Utilisateurs::query()
                ->where('role', RoleUtilisateur::Technicien)
                ->where('statut', 'actif')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();
        }

        if (in_array($user->role, [RoleUtilisateur::ClientAdmin, RoleUtilisateur::ClientUser], true)
            && $user->client_id !== null) {
            $ids = Requetes::query()
                ->where('client_id', $user->client_id)
                ->whereNotNull('technicien_id')
                ->distinct()
                ->pluck('technicien_id');

            return Utilisateurs::query()
                ->whereIn('id', $ids)
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();
        }

        return collect();
    }

    private function appliquerRechercheTexte(Builder $query, Request $request): void
    {
        $terme = trim((string) $request->input('q', ''));
        if ($terme === '') {
            return;
        }

        $like = '%'.addcslashes($terme, '%_\\').'%';
        $query->where(function (Builder $q) use ($like): void {
            $q->where('titre', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    private function appliquerFiltreDates(Builder $query, Request $request): void
    {
        if (! $request->filled('date_debut') && ! $request->filled('date_fin')) {
            return;
        }

        $axe = $request->input('axe_temporel', 'date_creation');
        if (! in_array($axe, self::AXES_TEMPORELS, true)) {
            $axe = 'date_creation';
        }

        $debut = $request->filled('date_debut')
            ? Carbon::parse($request->input('date_debut'))->startOfDay()
            : null;
        $fin = $request->filled('date_fin')
            ? Carbon::parse($request->input('date_fin'))->endOfDay()
            : null;

        match ($axe) {
            'date_creation' => $query
                ->when($debut, fn (Builder $q) => $q->where('date_creation', '>=', $debut))
                ->when($fin, fn (Builder $q) => $q->where('date_creation', '<=', $fin)),
            'date_intervention' => $query
                ->when($debut, fn (Builder $q) => $q->where('date_intervention', '>=', $debut))
                ->when($fin, fn (Builder $q) => $q->where('date_intervention', '<=', $fin)),
            'intervention_debut' => $query->whereHas('intervention', function (Builder $q) use ($debut, $fin): void {
                if ($debut !== null) {
                    $q->where('heure_debut', '>=', $debut);
                }
                if ($fin !== null) {
                    $q->where('heure_debut', '<=', $fin);
                }
            }),
            'intervention_fin' => $query->whereHas('intervention', function (Builder $q) use ($debut, $fin): void {
                if ($debut !== null) {
                    $q->where('heure_fin', '>=', $debut);
                }
                if ($fin !== null) {
                    $q->where('heure_fin', '<=', $fin);
                }
            }),
        };
    }
}
