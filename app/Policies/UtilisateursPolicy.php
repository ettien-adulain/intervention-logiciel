<?php

namespace App\Policies;

use App\Enums\RoleUtilisateur;
use App\Models\Utilisateurs;

/**
 * Phase 4 — CDC §4.2 : qui peut gérer quel compte `utilisateurs`.
 */
class UtilisateursPolicy
{
    /** Liste des comptes : super admin (tout) ou admin client (son entreprise uniquement). */
    public function viewAny(Utilisateurs $actor): bool
    {
        return $actor->estSuperAdmin()
            || $actor->role === RoleUtilisateur::ClientAdmin;
    }

    public function view(Utilisateurs $actor, Utilisateurs $cible): bool
    {
        if ($actor->estSuperAdmin()) {
            return true;
        }

        if ($actor->id === $cible->id) {
            return true;
        }

        if ($actor->role === RoleUtilisateur::ClientAdmin
            && $actor->client_id !== null
            && (int) $actor->client_id === (int) $cible->client_id) {
            return true;
        }

        return false;
    }

    public function create(Utilisateurs $actor): bool
    {
        return $actor->estSuperAdmin()
            || $actor->role === RoleUtilisateur::ClientAdmin;
    }

    public function update(Utilisateurs $actor, Utilisateurs $cible): bool
    {
        if ($actor->id === $cible->id) {
            return true;
        }

        if ($actor->estSuperAdmin()) {
            return true;
        }

        if ($actor->role === RoleUtilisateur::ClientAdmin
            && $actor->client_id !== null
            && (int) $actor->client_id === (int) $cible->client_id
            && ! $cible->estSuperAdmin()
            && $cible->role !== RoleUtilisateur::Technicien) {
            return true;
        }

        return false;
    }

    public function delete(Utilisateurs $actor, Utilisateurs $cible): bool
    {
        if ($actor->id === $cible->id) {
            return false;
        }

        if ($actor->estSuperAdmin()) {
            return true;
        }

        return $actor->role === RoleUtilisateur::ClientAdmin
            && $actor->client_id !== null
            && (int) $actor->client_id === (int) $cible->client_id
            && ! $cible->estSuperAdmin()
            && $cible->role !== RoleUtilisateur::Technicien;
    }
}
