<?php

namespace App\Services;

use App\Models\Interventions;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Phase 12 — CDC §4.10 : indicateurs agrégés, périmètre aligné sur {@see Requetes::scopeVisiblesPour()}.
 */
class RequeteReportingService
{
    public function __construct(
        private Utilisateurs $utilisateur,
        private Carbon $debut,
        private Carbon $fin,
    ) {}

    /**
     * @return array{
     *     requetes_creees: int,
     *     interventions_terminees: int,
     *     techniciens: Collection<int, array{technicien: Utilisateurs|null, volume: int, delai_moyen_heures: float|null}>,
     *     temps_moyen_resolution_heures: float|null,
     *     clients_actifs: Collection,
     *     titres_frequents: Collection,
     * }
     */
    public function resume(): array
    {
        return [
            'requetes_creees' => $this->nombreRequetesCreees(),
            'interventions_terminees' => $this->nombreInterventionsTermineesPeriode(),
            'techniciens' => $this->performanceParTechnicien(),
            'temps_moyen_resolution_heures' => $this->tempsMoyenResolutionHeures(),
            'clients_actifs' => $this->clientsLesPlusActifs(),
            'titres_frequents' => $this->titresLesPlusFrequents(),
        ];
    }

    private function nombreRequetesCreees(): int
    {
        return Requetes::query()
            ->visiblesPour($this->utilisateur)
            ->whereBetween('date_creation', [$this->debut, $this->fin])
            ->count();
    }

    private function nombreInterventionsTermineesPeriode(): int
    {
        return Interventions::query()
            ->whereHas('requete', fn (Builder $q) => $q->visiblesPour($this->utilisateur))
            ->where('statut', 'terminee')
            ->whereNotNull('heure_fin')
            ->whereBetween('heure_fin', [$this->debut, $this->fin])
            ->count();
    }

    /**
     * Volume et délai moyen (heures) entre création de la requête et fin d’intervention.
     *
     * @return Collection<int, array{technicien: Utilisateurs|null, volume: int, delai_moyen_heures: float|null}>
     */
    private function performanceParTechnicien(): Collection
    {
        $interventions = Interventions::query()
            ->whereHas('requete', fn (Builder $q) => $q->visiblesPour($this->utilisateur))
            ->where('statut', 'terminee')
            ->whereNotNull('heure_fin')
            ->whereBetween('heure_fin', [$this->debut, $this->fin])
            ->with([
                'requete:id,date_creation',
                'technicien:id,prenom,nom',
            ])
            ->get(['id', 'requete_id', 'technicien_id', 'heure_fin']);

        return $interventions
            ->groupBy('technicien_id')
            ->map(function (Collection $items): array {
                $delais = $items->map(function (Interventions $i): ?float {
                    $creation = $i->requete?->date_creation;
                    if ($creation === null) {
                        return null;
                    }

                    return (float) $creation->diffInHours($i->heure_fin);
                })->filter(fn (?float $h) => $h !== null);

                return [
                    'technicien' => $items->first()?->technicien,
                    'volume' => $items->count(),
                    'delai_moyen_heures' => $delais->isEmpty() ? null : round($delais->avg(), 1),
                ];
            })
            ->values()
            ->sortByDesc('volume')
            ->values();
    }

    private function tempsMoyenResolutionHeures(): ?float
    {
        $requetes = Requetes::query()
            ->visiblesPour($this->utilisateur)
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [$this->debut, $this->fin])
            ->get(['date_creation', 'date_fin']);

        $heures = $requetes->map(fn (Requetes $r): float => (float) $r->date_creation->diffInHours($r->date_fin));

        return $heures->isEmpty() ? null : round($heures->avg(), 1);
    }

    private function clientsLesPlusActifs(): Collection
    {
        return Requetes::query()
            ->visiblesPour($this->utilisateur)
            ->whereBetween('date_creation', [$this->debut, $this->fin])
            ->join('clients', 'clients.id', '=', 'requetes.client_id')
            ->selectRaw('requetes.client_id, clients.nom_entreprise, count(*) as total')
            ->groupBy('requetes.client_id', 'clients.nom_entreprise')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    private function titresLesPlusFrequents(): Collection
    {
        return Requetes::query()
            ->visiblesPour($this->utilisateur)
            ->whereBetween('date_creation', [$this->debut, $this->fin])
            ->whereNotNull('titre')
            ->where('titre', '!=', '')
            ->selectRaw('titre, count(*) as total')
            ->groupBy('titre')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }
}
