<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use App\Support\RequeteActionsEnAttente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequeteActionsEnAttenteTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{Client, Utilisateurs, Utilisateurs, Utilisateurs, Requetes} */
    private function jeuxDonnees(): array
    {
        $client = Client::query()->create([
            'nom_entreprise' => 'ACME Notif',
            'email' => 'contact@acme-notif.test',
            'statut' => 'actif',
        ]);
        $clientUser = Utilisateurs::query()->create([
            'email' => 'user@acme-notif.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'Dupont',
            'prenom' => 'Jean',
        ]);
        $superAdmin = Utilisateurs::query()->create([
            'email' => 'admin-notif@ycs.test',
            'password' => 'password',
            'role' => RoleUtilisateur::SuperAdmin,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'SA',
            'prenom' => 'Root',
        ]);
        $technicien = Utilisateurs::query()->create([
            'email' => 'tech-notif@ycs.test',
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
            'titre' => 'Test notif',
            'description' => null,
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
        ]);

        return [$client, $clientUser, $superAdmin, $technicien, $requete];
    }

    public function test_super_admin_voit_un_ticket_ouvert_sans_technicien(): void
    {
        [, , $superAdmin] = $this->jeuxDonnees();

        $this->assertSame(1, RequeteActionsEnAttente::countPour($superAdmin));
    }

    public function test_super_admin_plus_d_action_apres_planification(): void
    {
        [, , $superAdmin, $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($superAdmin)
            ->post(route('requetes.planifications.store', $requete), [
                'technicien_id' => $technicien->id,
                'date_intervention' => '2030-06-15T14:30',
                'message' => null,
            ])
            ->assertRedirect(route('requetes.show', $requete));

        $this->assertSame(0, RequeteActionsEnAttente::countPour($superAdmin));
    }

    public function test_client_voit_une_action_quand_planification_a_confirmer(): void
    {
        [, $clientUser, $superAdmin, $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($superAdmin)
            ->post(route('requetes.planifications.store', $requete), [
                'technicien_id' => $technicien->id,
                'date_intervention' => '2030-06-15T14:30',
                'message' => null,
            ]);

        $this->assertGreaterThanOrEqual(1, RequeteActionsEnAttente::countPour($clientUser));
        $this->assertContains($requete->id, RequeteActionsEnAttente::idsPour($clientUser));
    }

    public function test_technicien_voit_une_action_sans_intervention_sur_ticket_planifie(): void
    {
        [, , $superAdmin, $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($superAdmin)
            ->post(route('requetes.planifications.store', $requete), [
                'technicien_id' => $technicien->id,
                'date_intervention' => '2030-06-15T14:30',
                'message' => null,
            ]);

        $this->assertGreaterThanOrEqual(1, RequeteActionsEnAttente::countPour($technicien));
    }

    public function test_page_liste_requetes_contient_badge_actions(): void
    {
        [, $clientUser, $superAdmin, $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($superAdmin)
            ->post(route('requetes.planifications.store', $requete), [
                'technicien_id' => $technicien->id,
                'date_intervention' => '2030-06-15T14:30',
                'message' => null,
            ]);

        $this->actingAs($clientUser)
            ->get(route('requetes.index'))
            ->assertOk()
            ->assertSee('table-pending-badge', false);
    }

    public function test_menu_lateral_affiche_compteur_requetes(): void
    {
        [, $clientUser, $superAdmin, $technicien, $requete] = $this->jeuxDonnees();

        $this->actingAs($superAdmin)
            ->post(route('requetes.planifications.store', $requete), [
                'technicien_id' => $technicien->id,
                'date_intervention' => '2030-06-15T14:30',
                'message' => null,
            ]);

        $this->actingAs($clientUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('sidebar-badge', false);
    }
}
