<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientModuleTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): Utilisateurs
    {
        return Utilisateurs::query()->create([
            'email' => 'sa@test.fr',
            'password' => 'password',
            'role' => RoleUtilisateur::SuperAdmin,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'SA',
            'prenom' => 'Test',
        ]);
    }

    public function test_super_admin_peut_lister_les_clients(): void
    {
        $this->actingAs($this->superAdmin());
        Client::query()->create([
            'nom_entreprise' => 'ACME',
            'statut' => 'actif',
        ]);

        $response = $this->get(route('clients.index'));

        $response->assertOk();
        $response->assertSee('ACME');
    }

    public function test_invite_ne_peut_pas_acceder_aux_clients(): void
    {
        $this->get(route('clients.index'))->assertRedirect(route('login'));
    }

    public function test_super_admin_peut_creer_un_client(): void
    {
        $this->actingAs($this->superAdmin());

        $response = $this->post(route('clients.store'), [
            'nom_entreprise' => 'Nouvelle SC',
            'email' => 'contact@nouvelle-sc.test',
            'telephone' => '0102030405',
            'adresse' => "1 rue Test\n75000 Paris",
            'statut' => 'actif',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'nom_entreprise' => 'Nouvelle SC',
            'email' => 'contact@nouvelle-sc.test',
        ]);
    }

    public function test_admin_client_peut_voir_sa_fiche_mais_pas_la_liste_globale(): void
    {
        $client = Client::query()->create([
            'nom_entreprise' => 'PME Demo',
            'statut' => 'actif',
        ]);
        $admin = Utilisateurs::query()->create([
            'email' => 'ca@test.fr',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientAdmin,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'Admin',
            'prenom' => 'Client',
        ]);

        $this->actingAs($admin);
        $this->get(route('clients.index'))->assertForbidden();
        $this->get(route('clients.show', $client))->assertOk()->assertSee('PME Demo');
    }
}
