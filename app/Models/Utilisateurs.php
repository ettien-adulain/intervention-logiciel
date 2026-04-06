<?php

namespace App\Models;

use App\Enums\RoleUtilisateur;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateurs extends Authenticatable
{
    use Notifiable;

    protected $table = 'utilisateurs';

    protected $fillable = [
        'client_id',
        'nom',
        'prenom',
        'email',
        'password',
        'role',
        'statut',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => RoleUtilisateur::class,
        ];
    }

    /** CDC §4.2 / phase 2 : seuls les comptes `actif` peuvent se connecter. */
    public function estActif(): bool
    {
        return $this->statut === 'actif';
    }

    /**
     * Vérifie si l’utilisateur possède l’un des rôles donnés (enum ou string).
     *
     * Exemple : $user->aUnDesRoles(RoleUtilisateur::Technicien, RoleUtilisateur::SuperAdmin)
     */
    public function aUnDesRoles(RoleUtilisateur|string ...$roles): bool
    {
        $current = $this->role instanceof RoleUtilisateur
            ? $this->role->value
            : (string) $this->role;

        foreach ($roles as $r) {
            $val = $r instanceof RoleUtilisateur ? $r->value : $r;
            if ($current === $val) {
                return true;
            }
        }

        return false;
    }

    public function estSuperAdmin(): bool
    {
        return $this->role === RoleUtilisateur::SuperAdmin;
    }

    /** Multi-tenant : même entreprise que la fiche client passée en argument. */
    public function appartientAuClient(?Client $client): bool
    {
        if ($client === null || $this->client_id === null) {
            return false;
        }

        return (int) $this->client_id === (int) $client->id;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function requetesAuteur(): HasMany
    {
        return $this->hasMany(Requetes::class, 'user_id');
    }

    public function requetesTechnicien(): HasMany
    {
        return $this->hasMany(Requetes::class, 'technicien_id');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Interventions::class, 'technicien_id');
    }

    public function planifications(): HasMany
    {
        return $this->hasMany(Planification::class, 'technicien_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'user_id');
    }
}
