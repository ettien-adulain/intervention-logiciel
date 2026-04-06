<?php

namespace App\Http\Requests;

use App\Enums\RoleUtilisateur;
use App\Models\Utilisateurs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Utilisateurs::class);
    }

    protected function prepareForValidation(): void
    {
        // Admin client : `client_id` imposé par son entreprise (anti-fraude).
        if ($this->user()->role === RoleUtilisateur::ClientAdmin && $this->user()->client_id) {
            $this->merge(['client_id' => $this->user()->client_id]);
        }
    }

    public function rules(): array
    {
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

        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:utilisateurs,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in($rolesValues)],
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'client_id' => [
                'nullable',
                'exists:clients,id',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $actor = $this->user();
            $role = RoleUtilisateur::tryFrom((string) $this->input('role'));
            if (! $role) {
                return;
            }

            if ($actor->role === RoleUtilisateur::ClientAdmin) {
                if ($role !== RoleUtilisateur::ClientAdmin && $role !== RoleUtilisateur::ClientUser) {
                    $v->errors()->add('role', 'Rôle non autorisé pour un administrateur client.');
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
}
