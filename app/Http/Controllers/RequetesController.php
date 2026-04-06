<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Requetes;
use Illuminate\Http\Request;

class RequetesController extends Controller
{
    // 📄 LISTE DES REQUÊTES
    public function index()
    {
        $requetes = Requetes::with(['client','user','technicien','medias'])
            ->orderBy('id','desc')
            ->get();

        return response()->json($requetes);
    }

    // ➕ CRÉER UNE REQUÊTE
    public function store(Request $request)
    {
        // Phase 3 CDC : entreprise inactive → pas de nouvelle requête.
        $client = Client::query()->findOrFail($request->client_id);
        if (! $client->peutRecevoirNouvellesRequetes()) {
            return response()->json([
                'message' => 'Ce client est inactif : création de requête refusée.',
            ], 422);
        }

        $requete = Requetes::create([
            'client_id' => $request->client_id,
            'user_id' => $request->user_id,
            'titre' => $request->titre,
            'description' => $request->description,
            'urgence' => $request->urgence,
            'statut' => 'ouverte'
        ]);

        return response()->json([
            'message' => 'Requête créée avec succès',
            'data' => $requete
        ]);
    }

    // 👁️ AFFICHER UNE REQUÊTE
    public function show($id)
    {
        $requete = Requetes::with(['client', 'medias', 'intervention'])
            ->findOrFail($id);

        return response()->json($requete);
    }

    // ✏️ METTRE À JOUR
    public function update(Request $request, $id)
    {
        $requete = Requetes::findOrFail($id);
        $requete->update($request->all());

        return response()->json([
            'message' => 'Requête mise à jour'
        ]);
    }

    // ❌ SUPPRIMER
    public function destroy($id)
    {
        Requetes::destroy($id);

        return response()->json([
            'message' => 'Requête supprimée'
        ]);
    }
}
