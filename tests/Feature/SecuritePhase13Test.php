<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Log;
use App\Models\Medias;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecuritePhase13Test extends TestCase
{
    use RefreshDatabase;

    public function test_invite_ne_peut_pas_acceder_au_fichier_media(): void
    {
        Storage::fake('medias_interventions');
        $client = Client::query()->create(['nom_entreprise' => 'C', 'statut' => 'actif']);
        $user = Utilisateurs::query()->create([
            'email' => 'u@media13.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'U',
            'prenom' => 'M',
        ]);
        $requete = Requetes::query()->create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'titre' => 'T',
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
        ]);
        $this->actingAs($user)->post(route('requetes.medias.store', $requete), [
            'fichier' => UploadedFile::fake()->image('a.jpg', 10, 10),
        ]);
        $media = Medias::query()->first();

        Auth::logout();

        $this->get(route('requetes.medias.fichier', [$requete, $media]))
            ->assertRedirect(route('login'));
    }

    public function test_connexion_reussie_journalisee(): void
    {
        $u = Utilisateurs::query()->create([
            'email' => 'ok@sec13.test',
            'password' => 'password123',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'O',
            'prenom' => 'K',
        ]);

        $this->post('/login', [
            'email' => 'ok@sec13.test',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertTrue(
            Log::query()->where('action', 'connexion_reussie')->where('user_id', $u->id)->exists()
        );
    }

    public function test_connexion_echouee_journalisee_sans_user_id(): void
    {
        Utilisateurs::query()->create([
            'email' => 'bad@sec13.test',
            'password' => 'password123',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => null,
            'nom' => 'B',
            'prenom' => 'D',
        ]);

        $this->post('/login', [
            'email' => 'bad@sec13.test',
            'password' => 'mauvais',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(
            Log::query()->where('action', 'connexion_echouee')->whereNull('user_id')->exists()
        );
    }

    public function test_upload_extension_incoherente_avec_mime_rejetee(): void
    {
        Storage::fake('medias_interventions');
        $client = Client::query()->create(['nom_entreprise' => 'C2', 'statut' => 'actif']);
        $user = Utilisateurs::query()->create([
            'email' => 'u2@media13.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'U',
            'prenom' => '2',
        ]);
        $requete = Requetes::query()->create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'titre' => 'T',
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
        ]);

        $fichier = UploadedFile::fake()->create('tromperie.jpg', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('requetes.medias.store', $requete), ['fichier' => $fichier])
            ->assertSessionHasErrors('fichier');

        $this->assertDatabaseCount('medias', 0);
    }
}
