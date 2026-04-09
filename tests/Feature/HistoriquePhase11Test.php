<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoriquePhase11Test extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_filtre_par_client(): void
    {
        $c1 = Client::query()->create(['nom_entreprise' => 'A', 'email' => 'a@test', 'statut' => 'actif']);
        $c2 = Client::query()->create(['nom_entreprise' => 'B', 'email' => 'b@test', 'statut' => 'actif']);
        $admin = Utilisateurs::query()->create([
            'email' => 'sa@h.test',
            'password' => 'password',
            'role' => RoleUtilisateur::SuperAdmin,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'S',
            'prenom' => 'A',
        ]);
        $u1 = Utilisateurs::query()->create([
            'email' => 'u1@h.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $c1->id,
            'nom' => 'U',
            'prenom' => '1',
        ]);
        $u2 = Utilisateurs::query()->create([
            'email' => 'u2@h.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $c2->id,
            'nom' => 'U',
            'prenom' => '2',
        ]);
        Requetes::query()->create([
            'client_id' => $c1->id,
            'user_id' => $u1->id,
            'titre' => 'Chez A',
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
        ]);
        Requetes::query()->create([
            'client_id' => $c2->id,
            'user_id' => $u2->id,
            'titre' => 'Chez B',
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('historique.requetes', ['client_id' => $c1->id]));

        $response->assertOk();
        $response->assertSee('Chez A');
        $response->assertDontSee('Chez B');
    }

    public function test_utilisateur_client_filtre_par_technicien_assigne(): void
    {
        $client = Client::query()->create(['nom_entreprise' => 'ACME', 'email' => 'c@test', 'statut' => 'actif']);
        $tech1 = Utilisateurs::query()->create([
            'email' => 't1@h.test',
            'password' => 'password',
            'role' => RoleUtilisateur::Technicien,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'T',
            'prenom' => 'Un',
        ]);
        $tech2 = Utilisateurs::query()->create([
            'email' => 't2@h.test',
            'password' => 'password',
            'role' => RoleUtilisateur::Technicien,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'T',
            'prenom' => 'Deux',
        ]);
        $user = Utilisateurs::query()->create([
            'email' => 'cu@h.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'C',
            'prenom' => 'U',
        ]);
        Requetes::query()->create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'technicien_id' => $tech1->id,
            'titre' => 'Mission un',
            'urgence' => 'moyenne',
            'statut' => 'planifiee',
        ]);
        Requetes::query()->create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'technicien_id' => $tech2->id,
            'titre' => 'Mission deux',
            'urgence' => 'moyenne',
            'statut' => 'planifiee',
        ]);

        $response = $this->actingAs($user)
            ->get(route('historique.requetes', ['technicien_id' => $tech1->id]));

        $response->assertOk();
        $response->assertSee('Mission un');
        $response->assertDontSee('Mission deux');
    }

    public function test_recherche_texte_sur_titre(): void
    {
        $client = Client::query()->create(['nom_entreprise' => 'X', 'email' => 'x@test', 'statut' => 'actif']);
        $user = Utilisateurs::query()->create([
            'email' => 'ux@h.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'U',
            'prenom' => 'X',
        ]);
        Requetes::query()->create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'titre' => 'Panne routeur unique',
            'description' => 'Rien',
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
        ]);
        Requetes::query()->create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'titre' => 'Autre sujet',
            'description' => 'Texte',
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
        ]);

        $response = $this->actingAs($user)
            ->get(route('historique.requetes', ['q' => 'routeur']));

        $response->assertOk();
        $response->assertSee('Panne routeur unique');
        $response->assertDontSee('Autre sujet');
    }

    public function test_filtre_periode_sur_heure_debut_intervention(): void
    {
        $client = Client::query()->create(['nom_entreprise' => 'Y', 'email' => 'y@test', 'statut' => 'actif']);
        $tech = Utilisateurs::query()->create([
            'email' => 'ty@h.test',
            'password' => 'password',
            'role' => RoleUtilisateur::Technicien,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'T',
            'prenom' => 'Y',
        ]);
        $user = Utilisateurs::query()->create([
            'email' => 'uy@h.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'U',
            'prenom' => 'Y',
        ]);
        $rDans = Requetes::query()->create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'technicien_id' => $tech->id,
            'titre' => 'Dans plage',
            'urgence' => 'moyenne',
            'statut' => 'terminee',
        ]);
        $rDans->intervention()->create([
            'technicien_id' => $tech->id,
            'heure_debut' => '2031-06-15 10:00:00',
            'heure_fin' => '2031-06-15 11:00:00',
            'statut' => 'terminee',
        ]);
        $rHors = Requetes::query()->create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'technicien_id' => $tech->id,
            'titre' => 'Hors plage',
            'urgence' => 'moyenne',
            'statut' => 'terminee',
        ]);
        $rHors->intervention()->create([
            'technicien_id' => $tech->id,
            'heure_debut' => '2032-01-01 10:00:00',
            'heure_fin' => '2032-01-01 11:00:00',
            'statut' => 'terminee',
        ]);

        $response = $this->actingAs($user)
            ->get(route('historique.requetes', [
                'axe_temporel' => 'intervention_debut',
                'date_debut' => '2031-06-01',
                'date_fin' => '2031-06-30',
            ]));

        $response->assertOk();
        $response->assertSee('Dans plage');
        $response->assertDontSee('Hors plage');
    }

    public function test_technicien_ne_voit_que_ses_tickets_meme_avec_parametres(): void
    {
        $c1 = Client::query()->create(['nom_entreprise' => 'C1', 'email' => null, 'statut' => 'actif']);
        $c2 = Client::query()->create(['nom_entreprise' => 'C2', 'email' => null, 'statut' => 'actif']);
        $tech = Utilisateurs::query()->create([
            'email' => 'techiso@h.test',
            'password' => 'password',
            'role' => RoleUtilisateur::Technicien,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'T',
            'prenom' => 'Iso',
        ]);
        $u1 = Utilisateurs::query()->create([
            'email' => 'z1@h.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $c1->id,
            'nom' => 'Z',
            'prenom' => '1',
        ]);
        $u2 = Utilisateurs::query()->create([
            'email' => 'z2@h.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $c2->id,
            'nom' => 'Z',
            'prenom' => '2',
        ]);
        Requetes::query()->create([
            'client_id' => $c1->id,
            'user_id' => $u1->id,
            'technicien_id' => $tech->id,
            'titre' => 'Pour moi',
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
        ]);
        Requetes::query()->create([
            'client_id' => $c2->id,
            'user_id' => $u2->id,
            'technicien_id' => null,
            'titre' => 'Pas pour moi',
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
        ]);

        $response = $this->actingAs($tech)
            ->get(route('historique.requetes', [
                'client_id' => $c2->id,
            ]));

        $response->assertOk();
        $response->assertSee('Pour moi');
        $response->assertDontSee('Pas pour moi');
    }
}
