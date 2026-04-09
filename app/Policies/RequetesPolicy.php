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

    /** Confirmation de la date par le client (CDC §4.5). */
    public function confirmerPlanification(Utilisateurs $user, Requetes $requete): bool
    {
        if (! in_array($user->role, [RoleUtilisateur::ClientAdmin, RoleUtilisateur::ClientUser], true)) {
            return false;
        }

        return $user->client_id !== null
            && (int) $user->client_id === (int) $requete->client_id;
    }

    /** Validation « arrivée du technicien » (CDC §4.6). */
    public function validerArriveeClient(Utilisateurs $user, Requetes $requete): bool
    {
        return $this->clientPeutValiderIntervention($user, $requete);
    }

    /** Confirmation « intervention en cours » côté client (CDC §4.6). */
    public function validerInterventionEnCoursClient(Utilisateurs $user, Requetes $requete): bool
    {
        return $this->clientPeutValiderIntervention($user, $requete);
    }

    /** Validation « fin d’intervention » côté client (CDC §4.6). */
    public function validerFinInterventionClient(Utilisateurs $user, Requetes $requete): bool
    {
        return $this->clientPeutValiderIntervention($user, $requete);
    }

    /** Confirmation finale technicien (CDC §4.6). */
    public function validerFinTechnicien(Utilisateurs $user, Requetes $requete): bool
    {
        if ($user->role !== RoleUtilisateur::Technicien) {
            return false;
        }

        return $requete->technicien_id !== null
            && (int) $requete->technicien_id === (int) $user->id;
    }

    /** Compte rendu d’intervention terrain (CDC §4.7) : technicien assigné uniquement. */
    public function gererInterventionTerrain(Utilisateurs $user, Requetes $requete): bool
    {
        return $this->validerFinTechnicien($user, $requete);
    }

    /** Génération du PDF de reçu (CDC §4.8) : intervention terminée. */
    public function genererRecuPdf(Utilisateurs $user, Requetes $requete): bool
    {
        if (! $this->view($user, $requete)) {
            return false;
        }

        $intervention = $requete->intervention;

        return $intervention !== null && $intervention->statut === 'terminee';
    }

    /** Téléchargement du reçu PDF déjà généré. */
    public function telechargerRecuPdf(Utilisateurs $user, Requetes $requete): bool
    {
        if (! $this->view($user, $requete)) {
            return false;
        }

        $recu = $requete->recu;

        return $recu !== null
            && $recu->chemin_pdf !== null
            && $recu->chemin_pdf !== '';
    }

    private function clientPeutValiderIntervention(Utilisateurs $user, Requetes $requete): bool
    {
        if (! in_array($user->role, [RoleUtilisateur::ClientAdmin, RoleUtilisateur::ClientUser], true)) {
            return false;
        }

        if ($user->client_id === null || (int) $user->client_id !== (int) $requete->client_id) {
            return false;
        }

        return $requete->technicien_id !== null;
    }
}
