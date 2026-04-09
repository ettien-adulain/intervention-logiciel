<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Log;
use App\Models\Planification;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanificationPhase7Test extends TestCase
{
    use RefreshDatabase;

    /** @return array{Client, Utilisateurs, Utilisateurs, Utilisateurs, Requetes} */
    private function jeuxDonnees(): array
    {
        $client = Client::query()->create([
            'nom_entreprise' => 'ACME Planif',
            'email' => 'contact@acme-planif.test',
            'statut' => 'actif',
        ]);
        $clientUser = Utilisateurs::query()->create([
            'email' => 'user@acme-planif.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'Dupont',
            'prenom' => 'Jean',
        ]);
        $superAdmin = Utilisateurs::query()->create([
            'email' => 'admin@ycs.test',
            'password' => 'password',
            'role' => RoleUtilisateur::SuperAdmin,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'SA',
            'prenom' => 'Root',
        ]);
        $technicien = Utilisateurs::query()->create([
            'email' => 'tech@ycs.test',
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
            'titre' => 'Panne réseau',
            'description' => 'Test planif',
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
        ]);

        return [$client, $clientUser, $superAdmin, $technicien, $requete];
    }

    public function test_super_admin_cree_une_planification_et_trace_un_log(): void
    {
        [, , $superAdmin, $technicien, $requete] = $this->jeuxDonnees();

        $date = '2030-06-15T14:30';

        $this->actingAs($superAdmin)
            ->post(route('requetes.planifications.store', $requete), [
                'technicien_id' => $technicien->id,
                'date_intervention' => $date,
                'message' => 'Merci de libérer le local.',
            ])
            ->assertRedirect(route('requetes.show', $requete));

        $this->assertDatabaseCount('planifications', 1);
        $planif = Planification::query()->first();
        $this->assertSame('planifiee', $planif->statut);
        $this->assertSame($technicien->id, $planif->technicien_id);

        $requete->refresh();
        $this->assertSame('planifiee', $requete->statut);
        $this->assertSame($technicien->id, $requete->technicien_id);
        $this->assertNotNull($requete->date_planification);

        $this->assertTrue(
            Log::query()->where('action', 'planification_creee')->where('user_id', $superAdmin->id)->exists()
        );
    }

    public function test_utilisateur_client_peut_confirmer_une_planification_planifiee(): void
    {
        [, $clientUser, $superAdmin, $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($superAdmin)
            ->post(route('requetes.planifications.store', $requete), [
                'technicien_id' => $technicien->id,
                'date_intervention' => '2030-07-01T09:00',
                'message' => null,
            ]);

        $planif = Planification::query()->first();

        $this->actingAs($clientUser)
            ->patch(route('requetes.planifications.update', [$requete, $planif]), [
                'statut' => 'confirmee',
            ])
            ->assertRedirect(route('requetes.show', $requete));

        $planif->refresh();
        $this->assertSame('confirmee', $planif->statut);
    }

    public function test_utilisateur_client_ne_peut_pas_creer_de_planification(): void
    {
        [, $clientUser, , $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($clientUser)
            ->post(route('requetes.planifications.store', $requete), [
                'technicien_id' => $technicien->id,
                'date_intervention' => '2030-08-01T10:00',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('planifications', 0);
    }
}
