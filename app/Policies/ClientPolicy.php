<?php

namespace App\Policies;

use App\Enums\RoleUtilisateur;
use App\Models\Client;
use App\Models\Utilisateurs;

/**
 * Autorisations sur les fiches **clients** (entreprises).
 *
 * Règle CDC (résumé) :
 * - Super admin : gestion globale des clients.
 * - Admin / utilisateur client : accès limité à **leur** entreprise (`client_id`).
 */
class ClientPolicy
{
    /** Liste des clients (ex. page index admin). */
    public function viewAny(Utilisateurs $user): bool
    {
        return $user->role === RoleUtilisateur::SuperAdmin;
    }

    public function view(Utilisateurs $user, Client $client): bool
    {
        if ($user->role === RoleUtilisateur::SuperAdmin) {
            return true;
        }

        // Employés d’une entreprise : uniquement leur propre client.
        return $user->appartientAuClient($client);
    }

    public function create(Utilisateurs $user): bool
    {
        return $user->role === RoleUtilisateur::SuperAdmin;
    }

    public function update(Utilisateurs $user, Client $client): bool
    {
        if ($user->role === RoleUtilisateur::SuperAdmin) {
            return true;
        }

        // Option métier : seul l’admin client peut modifier la fiche de son entreprise.
        return $user->role === RoleUtilisateur::ClientAdmin
            && $user->appartientAuClient($client);
    }

    public function delete(Utilisateurs $user, Client $client): bool
    {
        return $user->role === RoleUtilisateur::SuperAdmin;
    }
}
