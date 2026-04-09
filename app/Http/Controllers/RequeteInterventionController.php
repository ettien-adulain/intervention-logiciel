<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveInterventionRequest;
use App\Models\Interventions;
use App\Models\Requetes;
use App\Support\Journalisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Phase 9 — CDC §4.7 : compte rendu d’intervention sur le terrain.
 */
class RequeteInterventionController extends Controller
{
    public function store(SaveInterventionRequest $request, Requetes $requete): RedirectResponse
    {
        if ($requete->intervention !== null) {
            return redirect()
                ->route('requetes.show', $requete)
                ->with('status', 'intervention_deja_existante');
        }

        $validated = $request->validated();

        $intervention = Interventions::query()->create([
            'requete_id' => $requete->id,
            'technicien_id' => (int) $requete->technicien_id,
            'rapport' => $validated['rapport'] ?? null,
            'pieces_utilisees' => $validated['pieces_utilisees'] ?? null,
            'heure_debut' => $validated['heure_debut'] ?? null,
            'heure_fin' => $validated['heure_fin'] ?? null,
            'statut' => $validated['statut'],
        ]);

        $this->synchroniserStatutRequete($requete, $intervention, $request);

        Journalisation::trace(
            $request,
            'intervention_creee',
            sprintf('Requête #%d (%s), intervention #%d', $requete->id, $requete->numeroTicket(), $intervention->id)
        );

        if ($intervention->statut === 'terminee') {
            Journalisation::trace(
                $request,
                'intervention_terminee',
                sprintf('Requête #%d, intervention #%d', $requete->id, $intervention->id)
            );
        }

        return redirect()
            ->route('requetes.show', $requete)
            ->with('status', 'intervention_creee');
    }

    public function update(SaveInterventionRequest $request, Requetes $requete): RedirectResponse
    {
        $intervention = $requete->intervention;
        if ($intervention === null) {
            return redirect()
                ->route('requetes.show', $requete)
                ->with('status', 'intervention_absente');
        }

        $validated = $request->validated();
        $ancienStatut = $intervention->statut;

        $intervention->update([
            'rapport' => $validated['rapport'] ?? null,
            'pieces_utilisees' => $validated['pieces_utilisees'] ?? null,
            'heure_debut' => $validated['heure_debut'] ?? null,
            'heure_fin' => $validated['heure_fin'] ?? null,
            'statut' => $validated['statut'],
        ]);

        $intervention->refresh();
        $this->synchroniserStatutRequete($requete->fresh(), $intervention, $request);

        Journalisation::trace(
            $request,
            'intervention_mise_a_jour',
            sprintf('Requête #%d, intervention #%d', $requete->id, $intervention->id)
        );

        if ($ancienStatut !== 'terminee' && $intervention->statut === 'terminee') {
            Journalisation::trace(
                $request,
                'intervention_terminee',
                sprintf('Requête #%d, intervention #%d', $requete->id, $intervention->id)
            );
        }

        return redirect()
            ->route('requetes.show', $requete)
            ->with('status', 'intervention_mise_a_jour');
    }

    private function synchroniserStatutRequete(Requetes $requete, Interventions $intervention, Request $request): void
    {
        $requete = $requete->fresh();
        if ($requete === null) {
            return;
        }

        $statutAvant = $requete->statut;

        if ($intervention->statut === 'terminee') {
            $requete->update([
                'statut' => 'terminee',
                'date_fin' => $intervention->heure_fin ?? now(),
            ]);
        } elseif (in_array($requete->statut, ['terminee', 'cloturee'], true)) {
            return;
        } else {
            $requete->update(['statut' => 'en_cours']);
        }

        $requete->refresh();
        if ($requete->statut !== $statutAvant) {
            Journalisation::trace(
                $request,
                'requete_statut_modifie',
                sprintf(
                    'Requête #%d (%s) : %s → %s',
                    $requete->id,
                    $requete->numeroTicket(),
                    $statutAvant,
                    $requete->statut
                )
            );
        }
    }
}
