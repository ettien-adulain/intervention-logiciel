<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequeteValidationRequest;
use App\Models\Requetes;
use App\Support\Journalisation;
use Illuminate\Http\RedirectResponse;

/**
 * Phase 8 — CDC §4.6 : validations horodatées client / technicien.
 */
class RequeteValidationController extends Controller
{
    public function store(StoreRequeteValidationRequest $request, Requetes $requete): RedirectResponse
    {
        $etape = $request->validated('etape');

        match ($etape) {
            'client_arrivee' => $this->authorize('validerArriveeClient', $requete),
            'client_intervention_en_cours' => $this->authorize('validerInterventionEnCoursClient', $requete),
            'client_fin' => $this->authorize('validerFinInterventionClient', $requete),
            'technicien_fin' => $this->authorize('validerFinTechnicien', $requete),
        };

        $validation = $requete->validation()->firstOrCreate(
            ['requete_id' => $requete->id],
            []
        );

        $now = now();
        $colonne = match ($etape) {
            'client_arrivee' => 'client_arrivee_at',
            'client_intervention_en_cours' => 'client_intervention_en_cours_at',
            'client_fin' => 'client_fin_at',
            'technicien_fin' => 'technicien_fin_at',
        };

        if ($validation->{$colonne} !== null) {
            return redirect()
                ->route('requetes.show', $requete)
                ->with('status', 'validation_deja_enregistree');
        }

        $validation->{$colonne} = $now;
        $validation->save();

        $action = match ($etape) {
            'client_arrivee' => 'validation_client_arrivee',
            'client_intervention_en_cours' => 'validation_client_intervention_en_cours',
            'client_fin' => 'validation_client_fin',
            'technicien_fin' => 'validation_technicien_fin',
        };

        Journalisation::trace(
            $request,
            $action,
            sprintf('Requête #%d (%s), étape %s', $requete->id, $requete->numeroTicket(), $etape)
        );

        $flash = match ($etape) {
            'client_arrivee' => 'validation_client_arrivee',
            'client_intervention_en_cours' => 'validation_client_intervention_en_cours',
            'client_fin' => 'validation_client_fin',
            'technicien_fin' => 'validation_technicien_fin',
        };

        return redirect()
            ->route('requetes.show', $requete)
            ->with('status', $flash);
    }
}
