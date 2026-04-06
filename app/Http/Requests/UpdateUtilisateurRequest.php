<?php

namespace App\Http\Requests;

use App\Enums\RoleUtilisateur;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cible = $this->route('utilisateur');

        return $cible instanceof Utilisateurs && $this->user()->can('update', $cible);
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()->role === RoleUtilisateur::ClientAdmin && $this->user()->client_id) {
            $this->merge(['client_id' => $this->user()->client_id]);
        }
    }

    public function rules(): array
    {
        /** @var Utilisateurs $cible */
        $cible = $this->route('utilisateur');
        $actor = $this->user();

        $rolesValues = match (true) {
            $actor->estSuperAdmin() => [
                RoleUtilisateur::SuperAdmin->value,
                RoleUtilisateur::ClientAdmin->value,
                RoleUtilisateur::ClientUser->value,
                RoleUtilisateur::Technicien->value,
            ],
            $actor->role === RoleUtilisateur::ClientAdmin => [
                RoleUtilisateur::ClientAdmin->value,
                RoleUtilisateur::ClientUser->value,
            ],
            default => [],
        };

        // Un utilisateur ne modifie que son propre profil (mot de passe / noms) — rôles figés.
        if ($actor->id === $cible->id && ! $actor->estSuperAdmin() && $actor->role !== RoleUtilisateur::ClientAdmin) {
            return [
                'nom' => ['required', 'string', 'max:255'],
                'prenom' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:191', Rule::unique('utilisateurs', 'email')->ignore($cible->id)],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            ];
        }

        if ($actor->id === $cible->id) {
            return [
                'nom' => ['required', 'string', 'max:255'],
                'prenom' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:191', Rule::unique('utilisateurs', 'email')->ignore($cible->id)],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
                'role' => ['required', 'string', Rule::in($rolesValues)],
                'statut' => ['required', Rule::in(['actif', 'inactif'])],
                'client_id' => ['nullable', 'exists:clients,id'],
            ];
        }

        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:191', Rule::unique('utilisateurs', 'email')->ignore($cible->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in($rolesValues)],
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'client_id' => ['nullable', 'exists:clients,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            /** @var Utilisateurs $cible */
            $cible = $this->route('utilisateur');
            $actor = $this->user();
            $role = RoleUtilisateur::tryFrom((string) $this->input('role'));
            if (! $role) {
                return;
            }

            if ($actor->role === RoleUtilisateur::ClientAdmin && $actor->id !== $cible->id) {
                if ($cible->estSuperAdmin() || $cible->role === RoleUtilisateur::Technicien) {
                    $v->errors()->add('role', 'Modification non autorisée pour ce compte.');
                }
            }

            if ($actor->estSuperAdmin()) {
                if (in_array($role, [RoleUtilisateur::ClientAdmin, RoleUtilisateur::ClientUser], true)
                    && ! $this->filled('client_id')) {
                    $v->errors()->add('client_id', 'Sélectionnez l’entreprise pour un compte client.');
                }
                if ($role === RoleUtilisateur::Technicien && $this->filled('client_id')) {
                    $v->errors()->add('client_id', 'Un technicien ne doit pas être rattaché à une entreprise cliente.');
                }
                if ($role === RoleUtilisateur::SuperAdmin && $this->filled('client_id')) {
                    $v->errors()->add('client_id', 'Un super administrateur ne doit pas avoir d’entreprise rattachée.');
                }
            }
        });
    }

    /** Données à persister (mot de passe ignoré si vide). */
    public function donneesSaufMotDePasseVide(): array
    {
        $data = $this->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
