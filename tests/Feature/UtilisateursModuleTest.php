<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtilisateursModuleTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): Utilisateurs
    {
        return Utilisateurs::query()->create([
            'email' => 'sa@phase4.test',
            'password' => 'password',
            'role' => RoleUtilisateur::SuperAdmin,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'SA',
            'prenom' => 'Test',
        ]);
    }

    public function test_super_admin_peut_creer_un_technicien_sans_client(): void
    {
        $this->actingAs($this->superAdmin());

        $response = $this->post(route('utilisateurs.store'), [
            'prenom' => 'Tech',
            'nom' => 'Un',
            'email' => 'tech@phase4.test',
            'password' => 'password12',
            'password_confirmation' => 'password12',
            'role' => RoleUtilisateur::Technicien->value,
            'statut' => 'actif',
            'client_id' => '',
        ]);

        $response->assertRedirect(route('utilisateurs.index'));
        $this->assertDatabaseHas('utilisateurs', [
            'email' => 'tech@phase4.test',
            'role' => RoleUtilisateur::Technicien->value,
            'client_id' => null,
        ]);
    }

    public function test_admin_client_peut_creer_un_utilisateur_dans_son_entreprise(): void
    {
        $client = Client::query()->create([
            'nom_entreprise' => 'PME Phase4',
            'statut' => 'actif',
        ]);
        $admin = Utilisateurs::query()->create([
            'email' => 'ca@phase4.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientAdmin,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'Admin',
            'prenom' => 'Client',
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('utilisateurs.store'), [
            'prenom' => 'User',
            'nom' => 'Simple',
            'email' => 'user@pme.test',
            'password' => 'password12',
            'password_confirmation' => 'password12',
            'role' => RoleUtilisateur::ClientUser->value,
            'statut' => 'actif',
        ]);

        $response->assertRedirect(route('utilisateurs.index'));
        $this->assertDatabaseHas('utilisateurs', [
            'email' => 'user@pme.test',
            'client_id' => $client->id,
            'role' => RoleUtilisateur::ClientUser->value,
        ]);
    }

    public function test_technicien_ne_peut_pas_lister_les_utilisateurs(): void
    {
        $tech = Utilisateurs::query()->create([
            'email' => 't@phase4.test',
            'password' => 'password',
            'role' => RoleUtilisateur::Technicien,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'T',
            'prenom' => 'Tech',
        ]);

        $this->actingAs($tech);
        $this->get(route('utilisateurs.index'))->assertForbidden();
    }
}
