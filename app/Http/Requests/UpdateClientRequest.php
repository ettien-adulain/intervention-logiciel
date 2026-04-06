<?php

namespace App\Http\Requests;

use App\Enums\RoleUtilisateur;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation mise à jour fiche client.
 * Le champ `statut` n’est modifiable que par le super admin (désactivation entreprise).
 */
class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');

        return $client && $this->user()->can('update', $client);
    }

    public function rules(): array
    {
        $rules = [
            'nom_entreprise' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string'],
        ];

        if ($this->user()->role === RoleUtilisateur::SuperAdmin) {
            $rules['statut'] = ['required', Rule::in(['actif', 'inactif'])];
        }

        return $rules;
    }

    /**
     * Données validées : sans `statut` si l’utilisateur n’est pas super admin
     * (évite qu’un admin client réactive une entreprise désactivée via requête forgée).
     */
    public function safeForClient(): array
    {
        $data = $this->validated();
        if ($this->user()->role !== RoleUtilisateur::SuperAdmin) {
            unset($data['statut']);
        }

        return $data;
    }
}
