<?php

namespace App\Http\Requests;

use App\Models\Planification;
use App\Models\Requetes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePlanificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $requete = $this->route('requete');
        $statut = $this->input('statut');

        if (! $requete instanceof Requetes || ! is_string($statut)) {
            return false;
        }

        return match ($statut) {
            'confirmee' => $this->user()->can('confirmerPlanification', $requete),
            'annulee' => $this->user()->can('assignerTechnicien', $requete),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'statut' => ['required', Rule::in(['confirmee', 'annulee'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            /** @var Planification|null $planif */
            $planif = $this->route('planification');
            if (! $planif instanceof Planification) {
                return;
            }

            $nouveau = $this->input('statut');
            if ($nouveau === 'confirmee' && $planif->statut !== 'planifiee') {
                $v->errors()->add('statut', 'Seule une planification « planifiée » peut être confirmée.');
            }
            if ($nouveau === 'annulee' && $planif->statut === 'annulee') {
                $v->errors()->add('statut', 'Cette planification est déjà annulée.');
            }
        });
    }
}
