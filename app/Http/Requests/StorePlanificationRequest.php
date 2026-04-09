<?php

namespace App\Http\Requests;

use App\Enums\RoleUtilisateur;
use App\Models\Requetes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $requete = $this->route('requete');

        return $requete instanceof Requetes
            && $this->user()->can('assignerTechnicien', $requete);
    }

    public function rules(): array
    {
        return [
            'technicien_id' => [
                'required',
                'integer',
                Rule::exists('utilisateurs', 'id')->where(function ($q) {
                    $q->where('role', RoleUtilisateur::Technicien->value)
                        ->where('statut', 'actif');
                }),
            ],
            'date_intervention' => ['required', 'date'],
            'message' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'technicien_id.exists' => 'Le technicien choisi est invalide ou inactif.',
        ];
    }
}
