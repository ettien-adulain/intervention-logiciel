<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Log;
use App\Models\Recus;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecusPhase10Test extends TestCase
{
    use RefreshDatabase;

    /** @return array{Client, Utilisateurs, Utilisateurs, Utilisateurs, Requetes} */
    private function jeuxDonneesInterventionTerminee(): array
    {
        $client = Client::query()->create([
            'nom_entreprise' => 'ACME Reçu',
            'email' => 'contact@acme-recu.test',
            'statut' => 'actif',
        ]);
        $clientUser = Utilisateurs::query()->create([
            'email' => 'user@acme-recu.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'Martin',
            'prenom' => 'Claire',
        ]);
        $superAdmin = Utilisateurs::query()->create([
            'email' => 'admin@recu.test',
            'password' => 'password',
            'role' => RoleUtilisateur::SuperAdmin,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'SA',
            'prenom' => 'Root',
        ]);
        $technicien = Utilisateurs::query()->create([
            'email' => 'tech@recu.test',
            'password' => 'password',
            'role' => RoleUtilisateur::Technicien,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'Tech',
            'prenom' => 'Paul',
        ]);
        $requete = Requetes::query()->create([
            'client_id' => $client->id,
            'user_id' => $clientUser->id,
            'technicien_id' => $technicien->id,
            'titre' => 'Panne',
            'description' => 'Test reçu',
            'urgence' => 'moyenne',
            'statut' => 'terminee',
        ]);

        $requete->intervention()->create([
            'technicien_id' => $technicien->id,
            'rapport' => 'Réparation OK.',
            'pieces_utilisees' => 'Filtre',
            'heure_debut' => '2030-09-01 08:00:00',
            'heure_fin' => '2030-09-01 10:00:00',
            'statut' => 'terminee',
        ]);

        return [$client, $clientUser, $superAdmin, $technicien, $requete];
    }

    public function test_technicien_genere_pdf_enregistre_recu_et_fichier(): void
    {
        [, , , $technicien, $requete] = $this->jeuxDonneesInterventionTerminee();
        Storage::fake(config('recus.disk'));

        $this->actingAs($technicien)
            ->post(route('requetes.recu.pdf.store', $requete))
            ->assertRedirect(route('requetes.show', $requete));

        $recu = Recus::query()->where('requete_id', $requete->id)->first();
        $this->assertNotNull($recu);
        $this->assertNotNull($recu->chemin_pdf);
        Storage::disk(config('recus.disk'))->assertExists($recu->chemin_pdf);

        $this->assertTrue(Log::query()->where('action', 'recu_pdf_genere')->where('user_id', $technicien->id)->exists());
    }

    public function test_telechargement_renvoi_un_pdf(): void
    {
        [, , , $technicien, $requete] = $this->jeuxDonneesInterventionTerminee();
        Storage::fake(config('recus.disk'));

        $this->actingAs($technicien)
            ->post(route('requetes.recu.pdf.store', $requete));

        $response = $this->actingAs($technicien)
            ->get(route('requetes.recu.pdf.download', $requete));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_sans_intervention_terminee_generation_interdite(): void
    {
        [, $clientUser, , $technicien, $requete] = $this->jeuxDonneesInterventionTerminee();
        $requete->intervention()->delete();
        $requete->update(['statut' => 'planifiee']);
        Storage::fake(config('recus.disk'));

        $this->actingAs($technicien)
            ->post(route('requetes.recu.pdf.store', $requete))
            ->assertForbidden();
    }

    public function test_autre_client_ne_peut_pas_telecharger(): void
    {
        [, , , $technicien, $requete] = $this->jeuxDonneesInterventionTerminee();
        Storage::fake(config('recus.disk'));

        $this->actingAs($technicien)
            ->post(route('requetes.recu.pdf.store', $requete));

        $autreEntreprise = Client::query()->create([
            'nom_entreprise' => 'Autre SA',
            'email' => null,
            'statut' => 'actif',
        ]);
        $autreClient = Utilisateurs::query()->create([
            'email' => 'autre@ailleurs.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $autreEntreprise->id,
            'nom' => 'X',
            'prenom' => 'Y',
        ]);

        $this->actingAs($autreClient)
            ->get(route('requetes.recu.pdf.download', $requete))
            ->assertForbidden();
    }
}
