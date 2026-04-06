<?php

namespace App\Enums;

/**
 * Rôles alignés sur la colonne `utilisateurs.role` (CDC §3 et §4.2).
 *
 * Utilisation :
 * - comparaison : $user->role === RoleUtilisateur::SuperAdmin
 * - middleware   : ->middleware('role:super_admin')  (valeur string en base)
 */
enum RoleUtilisateur: string
{
    case SuperAdmin = 'super_admin';
    case ClientAdmin = 'client_admin';
    case ClientUser = 'client_user';
    case Technicien = 'technicien';

    /** Libellé lisible pour l’interface (optionnel). */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super administrateur',
            self::ClientAdmin => 'Administrateur client',
            self::ClientUser => 'Utilisateur client',
            self::Technicien => 'Technicien',
        };
    }
}
