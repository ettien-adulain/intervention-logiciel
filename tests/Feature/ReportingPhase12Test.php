<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingPhase12Test extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_voit_les_indicateurs_agreges(): void
    {
        $c = Client::query()->create(['nom_entreprise' => 'ACME Rep', 'email' => 'r@test', 'statut' => 'actif']);
        $tech = Utilisateurs::query()->create([
            'email' => 'tech@rep.test',
            'password' => 'password',
            'role' => RoleUtilisateur::Technicien,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'T',
            'prenom' => 'One',
        ]);
        $u = Utilisateurs::query()->create([
            'email' => 'u@rep.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $c->id,
            'nom' => 'U',
            'prenom' => 'X',
        ]);
        $admin = Utilisateurs::query()->create([
            'email' => 'sa@rep.test',
            'password' => 'password',
            'role' => RoleUtilisateur::SuperAdmin,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'S',
            'prenom' => 'A',
        ]);

        $r = Requetes::query()->create([
            'client_id' => $c->id,
            'user_id' => $u->id,
            'technicien_id' => $tech->id,
            'titre' => 'Panne réseau',
            'urgence' => 'moyenne',
            'statut' => 'terminee',
            'date_creation' => '2030-06-01 08:00:00',
            'date_fin' => '2030-06-02 10:00:00',
        ]);
        $r->intervention()->create([
            'technicien_id' => $tech->id,
            'heure_debut' => '2030-06-02 08:00:00',
            'heure_fin' => '2030-06-02 10:00:00',
            'statut' => 'terminee',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reporting.index', [
                'date_debut' => '2030-06-01',
                'date_fin' => '2030-06-30',
            ]));

        $response->assertOk();
        $response->assertSee('Panne réseau');
        $response->assertSee('ACME Rep');
        $response->assertSee('One');
        $response->assertSee('26 h');
    }

    public function test_utilisateur_client_ne_voit_que_son_entreprise_dans_clients_actifs(): void
    {
        $c1 = Client::query()->create(['nom_entreprise' => 'Entreprise A', 'email' => null, 'statut' => 'actif']);
        $c2 = Client::query()->create(['nom_entreprise' => 'Entreprise B', 'email' => null, 'statut' => 'actif']);
        $u1 = Utilisateurs::query()->create([
            'email' => 'u1@rep2.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $c1->id,
            'nom' => 'U',
            'prenom' => '1',
        ]);
        $u2 = Utilisateurs::query()->create([
            'email' => 'u2@rep2.test',
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
            'titre' => 'T1',
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
            'date_creation' => '2031-03-10 10:00:00',
        ]);
        Requetes::query()->create([
            'client_id' => $c2->id,
            'user_id' => $u2->id,
            'titre' => 'T2',
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
            'date_creation' => '2031-03-15 10:00:00',
        ]);

        $response = $this->actingAs($u1)
            ->get(route('reporting.index', [
                'date_debut' => '2031-03-01',
                'date_fin' => '2031-03-31',
            ]));

        $response->assertOk();
        $response->assertSee('Entreprise A');
        $response->assertDontSee('Entreprise B');
    }

    public function test_technicien_ne_voit_que_ses_interventions_dans_performance(): void
    {
        $c = Client::query()->create(['nom_entreprise' => 'C', 'email' => null, 'statut' => 'actif']);
        $t1 = Utilisateurs::query()->create([
            'email' => 't1@rep3.test',
            'password' => 'password',
            'role' => RoleUtilisateur::Technicien,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'Alpha',
            'prenom' => 'Tech',
        ]);
        $t2 = Utilisateurs::query()->create([
            'email' => 't2@rep3.test',
            'password' => 'password',
            'role' => RoleUtilisateur::Technicien,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'Beta',
            'prenom' => 'Tech',
        ]);
        $u = Utilisateurs::query()->create([
            'email' => 'uc@rep3.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $c->id,
            'nom' => 'U',
            'prenom' => 'C',
        ]);
        $r1 = Requetes::query()->create([
            'client_id' => $c->id,
            'user_id' => $u->id,
            'technicien_id' => $t1->id,
            'titre' => 'R1',
            'urgence' => 'moyenne',
            'statut' => 'terminee',
            'date_creation' => '2032-01-01 08:00:00',
        ]);
        $r1->intervention()->create([
            'technicien_id' => $t1->id,
            'heure_fin' => '2032-01-02 10:00:00',
            'statut' => 'terminee',
        ]);
        $r2 = Requetes::query()->create([
            'client_id' => $c->id,
            'user_id' => $u->id,
            'technicien_id' => $t2->id,
            'titre' => 'R2',
            'urgence' => 'moyenne',
            'statut' => 'terminee',
            'date_creation' => '2032-01-01 08:00:00',
        ]);
        $r2->intervention()->create([
            'technicien_id' => $t2->id,
            'heure_fin' => '2032-01-03 10:00:00',
            'statut' => 'terminee',
        ]);

        $response = $this->actingAs($t1)
            ->get(route('reporting.index', [
                'date_debut' => '2032-01-01',
                'date_fin' => '2032-01-31',
            ]));

        $response->assertOk();
        $response->assertSee('Alpha');
        $response->assertDontSee('Beta');
    }
}
