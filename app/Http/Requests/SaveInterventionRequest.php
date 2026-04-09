<?php

namespace App\Http\Requests;

use App\Models\Interventions;
use App\Models\Requetes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveInterventionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $requete = $this->route('requete');
        if (! $requete instanceof Requetes) {
            return false;
        }

        return $this->user()?->can('gererInterventionTerrain', $requete) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $statutsAutorises = $this->statutsAutorisesPourSauvegarde();

        return [
            'rapport' => ['nullable', 'string'],
            'pieces_utilisees' => ['nullable', 'string'],
            'heure_debut' => ['nullable', 'date'],
            'heure_fin' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => $this->input('statut') === 'terminee'),
            ],
            'statut' => ['required', 'string', Rule::in($statutsAutorises)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $debut = $this->dateOuNull('heure_debut');
            $fin = $this->dateOuNull('heure_fin');
            if ($debut !== null && $fin !== null && $fin->lt($debut)) {
                $v->errors()->add('heure_fin', 'L’heure de fin doit être postérieure ou égale à l’heure de début.');
            }
        });
    }

    /**
     * @return list<string>
     */
    private function statutsAutorisesPourSauvegarde(): array
    {
        $existante = $this->interventionExistante();
        if ($existante !== null && $existante->statut === 'terminee') {
            return ['terminee'];
        }

        return ['en_cours', 'terminee'];
    }

    private function interventionExistante(): ?Interventions
    {
        $requete = $this->route('requete');
        if (! $requete instanceof Requetes) {
            return null;
        }

        return $requete->intervention;
    }

    private function dateOuNull(?string $cle): ?Carbon
    {
        $raw = $this->input($cle);
        if ($raw === null || $raw === '') {
            return null;
        }

        return Carbon::parse($raw);
    }
}
