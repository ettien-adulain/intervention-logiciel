<?php

namespace App\Services;

use App\Models\Requetes;
use Barryvdh\DomPDF\Facade\Pdf;

class RecuPdfService
{
    public function contenuPdf(Requetes $requete): string
    {
        $requete->loadMissing([
            'client',
            'user',
            'technicien',
            'intervention',
            'validation',
        ]);

        /* 80 mm (TPE / ticket) ≈ 226.77 pt de large ; hauteur suffisante pour le contenu */
        $largeurPt = 80 * 72 / 25.4;

        return Pdf::loadView('pdf.recu-intervention', ['requete' => $requete])
            ->setPaper([0, 0, $largeurPt, 2000], 'portrait')
            ->output();
    }
}
