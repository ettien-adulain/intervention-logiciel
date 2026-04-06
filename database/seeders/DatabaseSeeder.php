<?php

namespace Database\Seeders;

use App\Enums\RoleUtilisateur;
use App\Models\Utilisateurs;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Compte de démo pour tester la phase 2 après `php artisan migrate:fresh --seed`.
     *
     * Identifiants : admin@example.com / password
     * Changez le mot de passe en production.
     */
    public function run(): void
    {
        Utilisateurs::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'nom' => 'Administrateur',
                'prenom' => 'Super',
                'password' => 'password',
                'role' => RoleUtilisateur::SuperAdmin,
                'statut' => 'actif',
                'client_id' => null,
            ]
        );
    }
}
