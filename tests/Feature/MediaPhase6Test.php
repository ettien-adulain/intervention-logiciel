<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Medias;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaPhase6Test extends TestCase
{
    use RefreshDatabase;

    private function donneesRequete(): array
    {
        $client = Client::query()->create([
            'nom_entreprise' => 'ACME Media',
            'statut' => 'actif',
        ]);
        $auteur = Utilisateurs::query()->create([
            'email' => 'auteur@media.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $client->id,
            'nom' => 'A',
            'prenom' => 'U',
        ]);
        $requete = Requetes::query()->create([
            'client_id' => $client->id,
            'user_id' => $auteur->id,
            'titre' => 'Ticket avec média',
            'description' => 'Test',
            'urgence' => 'moyenne',
            'statut' => 'ouverte',
        ]);

        return [$client, $auteur, $requete];
    }

    public function test_utilisateur_client_peut_uploader_un_media_sur_sa_requete(): void
    {
        Storage::fake('medias_interventions');
        [, $auteur, $requete] = $this->donneesRequete();

        $file = UploadedFile::fake()->image('piece.jpg', 400, 300);

        $this->actingAs($auteur)
            ->post(route('requetes.medias.store', $requete), ['fichier' => $file])
            ->assertRedirect(route('requetes.show', $requete));

        $this->assertDatabaseCount('medias', 1);
        $media = Medias::query()->first();
        $this->assertSame('image', $media->type);
        Storage::disk('medias_interventions')->assertExists($media->chemin);
    }

    public function test_utilisateur_autre_client_ne_peut_pas_telecharger_le_media(): void
    {
        Storage::fake('medias_interventions');
        [$client, $auteur, $requete] = $this->donneesRequete();

        $file = UploadedFile::fake()->image('x.png', 100, 100);
        $this->actingAs($auteur)->post(route('requetes.medias.store', $requete), ['fichier' => $file]);

        $media = Medias::query()->first();
        $autreClient = Client::query()->create(['nom_entreprise' => 'Autre', 'statut' => 'actif']);
        $intrus = Utilisateurs::query()->create([
            'email' => 'autre@client.test',
            'password' => 'password',
            'role' => RoleUtilisateur::ClientUser,
            'statut' => 'actif',
            'client_id' => $autreClient->id,
            'nom' => 'X',
            'prenom' => 'Y',
        ]);

        $this->actingAs($intrus)
            ->get(route('requetes.medias.fichier', [$requete, $media]))
            ->assertForbidden();
    }
}
