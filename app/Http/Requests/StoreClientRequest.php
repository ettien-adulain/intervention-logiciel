<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation création client (super admin — phase 3 CDC §4.1).
 */
class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Client::class);
    }

    public function rules(): array
    {
        return [
            'nom_entreprise' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string'],
            'statut' => ['required', 'in:actif,inactif'],
        ];
    }
}
