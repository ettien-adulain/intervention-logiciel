<?php

namespace App\Http\Controllers;

use App\Models\Requetes;
use App\Services\RequeteReportingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Phase 12 — CDC §4.10 : reporting et statistiques (périmètre par rôle).
 */
class ReportingController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Requetes::class);

        $validated = $request->validate([
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $fin = isset($validated['date_fin'])
            ? Carbon::parse($validated['date_fin'])->endOfDay()
            : now()->endOfDay();
        $debut = isset($validated['date_debut'])
            ? Carbon::parse($validated['date_debut'])->startOfDay()
            : $fin->copy()->subDays(30)->startOfDay();

        if ($debut->gt($fin)) {
            $debut = $fin->copy()->subDays(30)->startOfDay();
        }

        $service = new RequeteReportingService($request->user(), $debut, $fin);
        $stats = $service->resume();

        return view('reporting.index', [
            'debut' => $debut,
            'fin' => $fin,
            'stats' => $stats,
        ]);
    }
}
