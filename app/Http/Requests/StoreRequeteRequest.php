<?php

namespace App\Http\Requests;

use App\Models\Requetes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequeteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Requetes::class);
    }

    public function rules(): array
    {
        $rules = [
            'titre' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'urgence' => ['required', Rule::in(['faible', 'moyenne', 'elevee'])],
        ];

        if ($this->user()->estSuperAdmin()) {
            $rules['client_id'] = ['required', 'integer', 'exists:clients,id'];
        }

        return $rules;
    }
}
