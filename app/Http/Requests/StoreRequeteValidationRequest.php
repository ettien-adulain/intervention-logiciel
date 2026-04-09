<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequeteValidationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'etape' => [
                'required',
                'string',
                Rule::in([
                    'client_arrivee',
                    'client_intervention_en_cours',
                    'client_fin',
                    'technicien_fin',
                ]),
            ],
        ];
    }
}
