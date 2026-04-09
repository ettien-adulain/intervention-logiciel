<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleUtilisateur;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_racine_redirige_un_invite_vers_la_connexion(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_connexion_redirige_vers_dashboard_avec_identifiants_valides(): void
    {
        Utilisateurs::query()->create([
            'email' => 'user@test.fr',
            'password' => 'secret123',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'Test',
            'prenom' => 'User',
        ]);

        $response = $this->post('/login', [
            'email' => 'user@test.fr',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(Utilisateurs::where('email', 'user@test.fr')->first());
    }

    public function test_compte_inactif_ne_peut_pas_se_connecter(): void
    {
        Utilisateurs::query()->create([
            'email' => 'inactif@test.fr',
            'password' => 'secret123',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'inactif',
            'client_id' => null,
            'nom' => 'Inactif',
            'prenom' => 'User',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactif@test.fr',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
