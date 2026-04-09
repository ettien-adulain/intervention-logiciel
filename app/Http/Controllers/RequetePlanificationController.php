<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanificationRequest;
use App\Http\Requests\UpdatePlanificationRequest;
use App\Models\Planification;
use App\Models\Requetes;
use App\Support\Journalisation;
use Illuminate\Http\RedirectResponse;

/**
 * Phase 7 — CDC §4.5 : planification, historique, visibilité dans l’application, trace dans `logs`.
 */
class RequetePlanificationController extends Controller
{
    public function store(StorePlanificationRequest $request, Requetes $requete): RedirectResponse
    {
        $validated = $request->validated();

        $planification = Planification::query()->create([
            'requete_id' => $requete->id,
            'technicien_id' => (int) $validated['technicien_id'],
            'date_intervention' => $validated['date_intervention'],
            'message' => $validated['message'] ?? null,
            'statut' => 'planifiee',
        ]);

        $requete->update([
            'technicien_id' => (int) $validated['technicien_id'],
            'date_planification' => $validated['date_intervention'],
            'date_intervention' => $validated['date_intervention'],
            'statut' => 'planifiee',
        ]);

        Journalisation::trace(
            $request,
            'planification_creee',
            sprintf(
                'Requête #%d (%s), planification #%d, technicien #%s (notification applicative uniquement)',
                $requete->id,
                $requete->numeroTicket(),
                $planification->id,
                $validated['technicien_id']
            )
        );

        return redirect()
            ->route('requetes.show', $requete)
            ->with('status', 'planification_creee');
    }

    public function update(UpdatePlanificationRequest $request, Requetes $requete, Planification $planification): RedirectResponse
    {
        $statut = $request->validated('statut');
        $planification->update(['statut' => $statut]);

        $message = $statut === 'confirmee' ? 'planification_confirmee' : 'planification_annulee';

        Journalisation::trace(
            $request,
            'planification_statut_'.$statut,
            sprintf('Requête #%d, planification #%d', $requete->id, $planification->id)
        );

        return redirect()
            ->route('requetes.show', $requete)
            ->with('status', $message);
    }
}
