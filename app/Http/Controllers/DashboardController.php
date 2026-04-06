<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Tableau de bord minimal après connexion.
 * Les modules (clients, requêtes…) viendront enrichir cette page ou des sous-routes.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard');
    }
}
