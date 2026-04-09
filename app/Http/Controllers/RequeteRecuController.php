<?php

namespace App\Http\Controllers;

use App\Models\Recus;
use App\Models\Requetes;
use App\Services\RecuPdfService;
use App\Support\Journalisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Phase 10 — CDC §4.8 : reçu PDF, stockage, téléchargement (uniquement dans l’application).
 */
class RequeteRecuController extends Controller
{
    public function __construct(
        private RecuPdfService $recuPdfService,
    ) {}

    public function store(Request $request, Requetes $requete): RedirectResponse
    {
        $this->authorize('genererRecuPdf', $requete);

        $requete->loadMissing('intervention');
        $disk = config('recus.disk');
        $chemin = 'requetes/'.$requete->id.'/recu.pdf';

        $binaire = $this->recuPdfService->contenuPdf($requete);
        Storage::disk($disk)->put($chemin, $binaire);

        Recus::query()->updateOrCreate(
            ['requete_id' => $requete->id],
            ['chemin_pdf' => $chemin]
        );

        Journalisation::trace(
            $request,
            'recu_pdf_genere',
            sprintf('Requête #%d (%s)', $requete->id, $requete->numeroTicket())
        );

        return redirect()
            ->route('requetes.show', $requete)
            ->with('status', 'recu_pdf_genere');
    }

    public function download(Request $request, Requetes $requete): Response
    {
        $this->authorize('telechargerRecuPdf', $requete);

        $requete->loadMissing('recu', 'client');
        $recu = $requete->recu;
        if ($recu === null || $recu->chemin_pdf === null || $recu->chemin_pdf === '') {
            abort(404);
        }

        $disk = config('recus.disk');
        if (! Storage::disk($disk)->exists($recu->chemin_pdf)) {
            abort(404);
        }

        $binaire = Storage::disk($disk)->get($recu->chemin_pdf);
        if ($binaire === false || $binaire === '') {
            abort(404);
        }

        $nomBase = preg_replace('/[^A-Za-z0-9\-]/', '_', $requete->numeroTicket());
        $nomFichier = ($nomBase !== '' ? 'recu_'.$nomBase : 'recu_'.$requete->id).'.pdf';

        Journalisation::trace(
            $request,
            'recu_pdf_telecharge',
            sprintf('Requête #%d (%s)', $requete->id, $requete->numeroTicket())
        );

        return response($binaire, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$nomFichier.'"',
            'Content-Length' => (string) strlen($binaire),
            'Cache-Control' => 'private, must-revalidate',
        ]);
    }
}
