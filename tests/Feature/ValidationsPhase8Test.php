<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Log;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use App\Models\Validations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationsPhase8Test extends TestCase
{
    use RefreshDatabase;

    /** @return array{Client, Utilisateurs, Utilisateurs, Utilisateurs, Requetes} */
    private function jeuxDonnees(): array
    {
        $client = Client::query()->create([
            'nom_entreprise' => 'ACME Valid',
            'email' => 'contact@acme-valid.test',
            'statut' => 'actif',
        ]);
        $clientUser = Utilisateurs::query()->create([
            'email' => 'user@acme-valid.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'Martin',
            'prenom' => 'Claire',
        ]);
        $superAdmin = Utilisateurs::query()->create([
            'email' => 'admin@valid.test',
            'password' => 'password',
            'role' => RoleUtilisateur::SuperAdmin,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'SA',
            'prenom' => 'Root',
        ]);
        $technicien = Utilisateurs::query()->create([
            'email' => 'tech@valid.test',
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
            'description' => 'Test validations',
            'urgence' => 'moyenne',
            'statut' => 'planifiee',
        ]);

        return [$client, $clientUser, $superAdmin, $technicien, $requete];
    }

    public function test_client_enregistre_arrivee_intervention_en_cours_et_fin_avec_logs(): void
    {
        [, $clientUser, , , $requete] = $this->jeuxDonnees();

        foreach (['client_arrivee', 'client_intervention_en_cours', 'client_fin'] as $etape) {
            $this->actingAs($clientUser)
                ->post(route('requetes.validations.store', $requete), ['etape' => $etape])
                ->assertRedirect(route('requetes.show', $requete));
        }

        $requete->load('validation');
        $this->assertNotNull($requete->validation);
        $this->assertNotNull($requete->validation->client_arrivee_at);
        $this->assertNotNull($requete->validation->client_intervention_en_cours_at);
        $this->assertNotNull($requete->validation->client_fin_at);

        $this->assertTrue(Log::query()->where('action', 'validation_client_arrivee')->where('user_id', $clientUser->id)->exists());
        $this->assertTrue(Log::query()->where('action', 'validation_client_intervention_en_cours')->exists());
        $this->assertTrue(Log::query()->where('action', 'validation_client_fin')->exists());
    }

    public function test_technicien_enregistre_fin_et_une_seule_ligne_validation_par_requete(): void
    {
        [, , , $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($technicien)
            ->post(route('requetes.validations.store', $requete), ['etape' => 'technicien_fin'])
            ->assertRedirect(route('requetes.show', $requete));

        $this->assertDatabaseCount('validations', 1);
        $this->assertNotNull(Validations::query()->first()->technicien_fin_at);
        $this->assertTrue(Log::query()->where('action', 'validation_technicien_fin')->where('user_id', $technicien->id)->exists());
    }

    public function test_client_sans_technicien_assigne_recoit_403(): void
    {
        [, $clientUser, , $technicien, $requete] = $this->jeuxDonnees();
        $requete->update(['technicien_id' => null]);

        $this->actingAs($clientUser)
            ->post(route('requetes.validations.store', $requete), ['etape' => 'client_arrivee'])
            ->assertForbidden();
    }

    public function test_autre_technicien_ne_peut_pas_valider_la_fin(): void
    {
        [, , , $technicien, $requete] = $this->jeuxDonnees();

        $autreTech = Utilisateurs::query()->create([
            'email' => 'tech2@valid.test',
            'password' => 'password',
            'role' => RoleUtilisateur::Technicien,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'Autre',
            'prenom' => 'Tech',
        ]);

        $this->actingAs($autreTech)
            ->post(route('requetes.validations.store', $requete), ['etape' => 'technicien_fin'])
            ->assertForbidden();
    }

    public function test_deuxieme_soumission_meme_etape_est_idempotente_sans_nouveau_log(): void
    {
        [, $clientUser, , , $requete] = $this->jeuxDonnees();

        $this->actingAs($clientUser)
            ->post(route('requetes.validations.store', $requete), ['etape' => 'client_arrivee']);

        $countAvant = Log::query()->where('action', 'validation_client_arrivee')->count();

        $this->actingAs($clientUser)
            ->post(route('requetes.validations.store', $requete), ['etape' => 'client_arrivee'])
            ->assertRedirect(route('requetes.show', $requete))
            ->assertSessionHas('status', 'validation_deja_enregistree');

        $this->assertSame($countAvant, Log::query()->where('action', 'validation_client_arrivee')->count());
    }
}
