<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Interventions;
use App\Models\Log;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterventionsPhase9Test extends TestCase
{
    use RefreshDatabase;

    /** @return array{Client, Utilisateurs, Utilisateurs, Utilisateurs, Requetes} */
    private function jeuxDonnees(): array
    {
        $client = Client::query()->create([
            'nom_entreprise' => 'ACME Inter',
            'email' => 'contact@acme-inter.test',
            'statut' => 'actif',
        ]);
        $clientUser = Utilisateurs::query()->create([
            'email' => 'user@acme-inter.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'Martin',
            'prenom' => 'Claire',
        ]);
        $superAdmin = Utilisateurs::query()->create([
            'email' => 'admin@inter.test',
            'password' => 'password',
            'role' => RoleUtilisateur::SuperAdmin,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'SA',
            'prenom' => 'Root',
        ]);
        $technicien = Utilisateurs::query()->create([
            'email' => 'tech@inter.test',
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
            'description' => 'Test intervention',
            'urgence' => 'moyenne',
            'statut' => 'planifiee',
        ]);

        return [$client, $clientUser, $superAdmin, $technicien, $requete];
    }

    public function test_technicien_cree_intervention_en_cours_et_met_la_requete_en_cours(): void
    {
        [, , , $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($technicien)
            ->post(route('requetes.intervention.store', $requete), [
                'rapport' => 'Diagnostic effectué.',
                'pieces_utilisees' => null,
                'heure_debut' => '2030-08-01T09:00',
                'heure_fin' => null,
                'statut' => 'en_cours',
            ])
            ->assertRedirect(route('requetes.show', $requete));

        $this->assertDatabaseCount('interventions', 1);
        $intervention = Interventions::query()->first();
        $this->assertSame('en_cours', $intervention->statut);
        $this->assertSame($technicien->id, $intervention->technicien_id);

        $requete->refresh();
        $this->assertSame('en_cours', $requete->statut);

        $this->assertTrue(Log::query()->where('action', 'intervention_creee')->where('user_id', $technicien->id)->exists());
    }

    public function test_technicien_passe_terminee_avec_heure_fin_met_requete_terminee_et_trace_terminee(): void
    {
        [, , , $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($technicien)
            ->post(route('requetes.intervention.store', $requete), [
                'rapport' => 'Début',
                'statut' => 'en_cours',
            ]);

        $this->actingAs($technicien)
            ->patch(route('requetes.intervention.update', $requete), [
                'rapport' => 'Remplacement module.',
                'pieces_utilisees' => 'Module X',
                'heure_debut' => '2030-08-01T09:00',
                'heure_fin' => '2030-08-01T11:30',
                'statut' => 'terminee',
            ])
            ->assertRedirect(route('requetes.show', $requete));

        $requete->refresh();
        $this->assertSame('terminee', $requete->statut);
        $this->assertNotNull($requete->date_fin);

        $this->assertTrue(Log::query()->where('action', 'intervention_terminee')->exists());
    }

    public function test_utilisateur_client_ne_peut_pas_creer_d_intervention(): void
    {
        [, $clientUser, , , $requete] = $this->jeuxDonnees();

        $this->actingAs($clientUser)
            ->post(route('requetes.intervention.store', $requete), [
                'statut' => 'en_cours',
            ])
            ->assertForbidden();
    }

    public function test_autre_technicien_ne_peut_pas_modifier(): void
    {
        [, , , $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($technicien)
            ->post(route('requetes.intervention.store', $requete), ['statut' => 'en_cours']);

        $autre = Utilisateurs::query()->create([
            'email' => 'tech2@inter.test',
            'password' => 'password',
            'role' => RoleUtilisateur::Technicien,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'Autre',
            'prenom' => 'T',
        ]);

        $this->actingAs($autre)
            ->patch(route('requetes.intervention.update', $requete), [
                'rapport' => 'Hack',
                'statut' => 'en_cours',
            ])
            ->assertForbidden();
    }

    public function test_deuxieme_creation_renvoi_message_sans_doublon(): void
    {
        [, , , $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($technicien)
            ->post(route('requetes.intervention.store', $requete), ['statut' => 'en_cours']);

        $this->actingAs($technicien)
            ->post(route('requetes.intervention.store', $requete), ['statut' => 'en_cours'])
            ->assertRedirect(route('requetes.show', $requete))
            ->assertSessionHas('status', 'intervention_deja_existante');

        $this->assertDatabaseCount('interventions', 1);
    }

    public function test_statut_terminee_sans_heure_fin_est_refuse(): void
    {
        [, , , $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($technicien)
            ->post(route('requetes.intervention.store', $requete), [
                'statut' => 'terminee',
                'heure_fin' => '',
            ])
            ->assertSessionHasErrors('heure_fin');
    }
}
