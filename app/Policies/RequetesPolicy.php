<?php

namespace App\Policies;

use App\Enums\RoleUtilisateur;
use App\Models\Requetes;
use App\Models\Utilisateurs;

/**
 * Autorisations sur les **requêtes / tickets**.
 *
 * À appeler dans les contrôleurs : $this->authorize('view', $requete);
 * Ou dans Blade : @can('update', $requete)
 *
 * Complétez au fil des phases (médias, validations, etc.).
 */
class RequetesPolicy
{
    public function viewAny(Utilisateurs $user): bool
    {
        // Tout rôle métier peut voir une liste — le **filtrage** se fait dans le contrôleur
        // (scopes : client_id, technicien_id, etc.).
        return true;
    }

    public function view(Utilisateurs $user, Requetes $requete): bool
    {
        if ($user->role === RoleUtilisateur::SuperAdmin) {
            return true;
        }

        if (in_array($user->role, [RoleUtilisateur::ClientAdmin, RoleUtilisateur::ClientUser], true)) {
            return $user->client_id !== null
                && (int) $user->client_id === (int) $requete->client_id;
        }

        if ($user->role === RoleUtilisateur::Technicien) {
            return $requete->technicien_id !== null
                && (int) $requete->technicien_id === (int) $user->id;
        }

        return false;
    }

    public function create(Utilisateurs $user): bool
    {
        return in_array($user->role, [
            RoleUtilisateur::SuperAdmin,
            RoleUtilisateur::ClientAdmin,
            RoleUtilisateur::ClientUser,
        ], true);
    }

    public function update(Utilisateurs $user, Requetes $requete): bool
    {
        return $this->view($user, $requete);
    }

    public function delete(Utilisateurs $user, Requetes $requete): bool
    {
        return $user->role === RoleUtilisateur::SuperAdmin;
    }

    /**
     * Exemple : assigner un technicien (phase planification).
     * À brancher sur une route dédiée plus tard.
     */
    public function assignerTechnicien(Utilisateurs $user, Requetes $requete): bool
    {
        return $user->role === RoleUtilisateur::SuperAdmin;
    }
}
