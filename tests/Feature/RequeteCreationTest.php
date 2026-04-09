<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequeteCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_utilisateur_client_peut_creer_une_requete_pour_son_entreprise(): void
    {
        $client = Client::query()->create([
            'nom_entreprise' => 'PME Création',
            'statut' => 'actif',
        ]);
        $user = Utilisateurs::query()->create([
            'email' => 'user@pme-creation.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'U',
            'prenom' => 'Jean',
        ]);

        $this->actingAs($user)
            ->post(route('requetes.store'), [
                'titre' => 'Panne lente',
                'description' => 'Le réseau coupe.',
                'urgence' => 'elevee',
            ])
            ->assertRedirect();

        $requete = Requetes::query()->first();
        $this->assertNotNull($requete);
        $this->assertSame($client->id, $requete->client_id);
        $this->assertSame($user->id, $requete->user_id);
        $this->assertSame('ouverte', $requete->statut);
        $this->assertSame('elevee', $requete->urgence);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{2,4}-\d{12}-\d+$/', $requete->numeroTicket());
        $this->assertStringStartsWith('PMEC-', $requete->numeroTicket());
    }

    public function test_super_admin_cree_une_requete_pour_un_client_choisi(): void
    {
        $client = Client::query()->create([
            'nom_entreprise' => 'Client SA',
            'statut' => 'actif',
        ]);
        $sa = Utilisateurs::query()->create([
            'email' => 'sa@test.fr',
            'password' => 'password',
            'role' => RoleUtilisateur::SuperAdmin,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'A',
            'prenom' => 'B',
        ]);

        $this->actingAs($sa)
            ->post(route('requetes.store'), [
                'client_id' => $client->id,
                'titre' => 'Ticket interne',
                'urgence' => 'moyenne',
            ])
            ->assertRedirect();

        $requete = Requetes::query()->first();
        $this->assertSame($client->id, $requete->client_id);
        $this->assertSame($sa->id, $requete->user_id);
    }

    public function test_client_inactif_refuse_la_creation(): void
    {
        $client = Client::query()->create([
            'nom_entreprise' => 'Fermé',
            'statut' => 'inactif',
        ]);
        $user = Utilisateurs::query()->create([
            'email' => 'u@ferme.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientAdmin,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'X',
            'prenom' => 'Y',
        ]);

        $this->actingAs($user)
            ->post(route('requetes.store'), [
                'urgence' => 'faible',
            ])
            ->assertSessionHasErrors('client_id');

        $this->assertDatabaseCount('requetes', 0);
    }

    public function test_technicien_ne_peut_pas_creer_de_requete(): void
    {
        $tech = Utilisateurs::query()->create([
            'email' => 't@test.fr',
            'password' => 'password',
            'role' => RoleUtilisateur::Technicien,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'T',
            'prenom' => 'T',
        ]);

        $this->actingAs($tech)
            ->post(route('requetes.store'), [
                'urgence' => 'moyenne',
            ])
            ->assertForbidden();
    }
}
