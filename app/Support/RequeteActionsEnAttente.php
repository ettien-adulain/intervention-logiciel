<?php

namespace App\Support;

use App\Enums\RoleUtilisateur;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use Illuminate\Database\Eloquent\Builder;

/**
 * Repère les tickets où l’utilisateur connecté a une action à faire dans l’application
 * (sans e-mail) : planif à confirmer, validations, intervention, planification support, etc.
 */
final class RequeteActionsEnAttente
{
    public static function countPour(Utilisateurs $user): int
    {
        return (int) self::queryPour($user)->count();
    }

    /**
     * @return list<int>
     */
    public static function idsPour(Utilisateurs $user): array
    {
        return self::queryPour($user)->pluck('id')->all();
    }

    /**
     * @param  Requetes  $requete  relations utiles : planifications, validation, intervention
     */
    public static function pourCetteRequete(Requetes $requete, Utilisateurs $user): bool
    {
        if ($user->estSuperAdmin()) {
            return in_array($requete->statut, ['ouverte', 'en_attente'], true)
                && $requete->technicien_id === null;
        }

        if (in_array($user->role, [RoleUtilisateur::ClientAdmin, RoleUtilisateur::ClientUser], true)) {
            if ($user->client_id === null || (int) $user->client_id !== (int) $requete->client_id) {
                return false;
            }

            foreach ($requete->planifications as $p) {
                if ($p->statut === 'planifiee') {
                    return true;
                }
            }

            return self::clientAUnSuiviValidationOuvert($requete);
        }

        if ($user->role === RoleUtilisateur::Technicien) {
            if ($requete->technicien_id === null || (int) $requete->technicien_id !== (int) $user->id) {
                return false;
            }

            if (in_array($requete->statut, ['terminee', 'cloturee'], true)) {
                $v = $requete->validation;

                return $v !== null
                    && $v->client_fin_at !== null
                    && $v->technicien_fin_at === null;
            }

            $intervention = $requete->intervention;
            if ($intervention === null) {
                return in_array($requete->statut, ['planifiee', 'en_cours', 'en_attente'], true);
            }

            if ($intervention->statut === 'en_cours') {
                return true;
            }

            $v = $requete->validation;

            return $v !== null
                && $v->client_fin_at !== null
                && $v->technicien_fin_at === null;
        }

        return false;
    }

    private static function clientAUnSuiviValidationOuvert(Requetes $requete): bool
    {
        if ($requete->technicien_id === null || in_array($requete->statut, ['terminee', 'cloturee'], true)) {
            return false;
        }

        $v = $requete->validation;
        if ($v === null) {
            return true;
        }

        if ($v->client_arrivee_at === null) {
            return true;
        }
        if ($v->client_intervention_en_cours_at === null) {
            return true;
        }
        if ($v->client_fin_at === null) {
            return true;
        }

        return false;
    }

    private static function queryPour(Utilisateurs $user): Builder
    {
        $base = Requetes::query()->visiblesPour($user);

        if ($user->estSuperAdmin()) {
            return $base
                ->whereNull('technicien_id')
                ->whereIn('statut', ['ouverte', 'en_attente']);
        }

        if (in_array($user->role, [RoleUtilisateur::ClientAdmin, RoleUtilisateur::ClientUser], true)) {
            if ($user->client_id === null) {
                return $base->whereRaw('1 = 0');
            }

            return $base->where(function (Builder $q) {
                $q->whereHas('planifications', fn (Builder $p) => $p->where('statut', 'planifiee'))
                    ->orWhere(function (Builder $q2) {
                        self::scopeValidationsClientEnAttente($q2);
                    });
            });
        }

        if ($user->role === RoleUtilisateur::Technicien) {
            return $base->where(function (Builder $q) {
                $q->where(function (Builder $q2) {
                    $q2->whereDoesntHave('intervention')
                        ->whereIn('statut', ['planifiee', 'en_cours', 'en_attente']);
                })
                    ->orWhereHas('intervention', fn (Builder $i) => $i->where('statut', 'en_cours'))
                    ->orWhereHas('validation', function (Builder $v) {
                        $v->whereNotNull('client_fin_at')
                            ->whereNull('technicien_fin_at');
                    });
            });
        }

        return $base->whereRaw('1 = 0');
    }

    private static function scopeValidationsClientEnAttente(Builder $q): void
    {
        $q->whereNotNull('technicien_id')
            ->whereNotIn('statut', ['terminee', 'cloturee'])
            ->where(function (Builder $inner) {
                $inner->whereDoesntHave('validation')
                    ->orWhereHas('validation', function (Builder $v) {
                        $v->where(function (Builder $w) {
                            $w->whereNull('client_arrivee_at')
                                ->orWhere(function (Builder $w2) {
                                    $w2->whereNotNull('client_arrivee_at')
                                        ->whereNull('client_intervention_en_cours_at');
                                })
                                ->orWhere(function (Builder $w3) {
                                    $w3->whereNotNull('client_intervention_en_cours_at')
                                        ->whereNull('client_fin_at');
                                });
                        });
                    });
            });
    }
}
