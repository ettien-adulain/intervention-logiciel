<?php

namespace App\Http\Requests;

use App\Models\Requetes;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $requete = $this->route('requete');

        return $requete instanceof Requetes
            && $this->user()->can('update', $requete);
    }

    public function rules(): array
    {
        $maxKo = config('medias.max_upload_ko', 20480);
        $mimesAutorises = config('medias.mimes', []);
        $extensions = config('medias.extensions_autorisees', []);
        $mimeParExtension = config('medias.mime_par_extension', []);

        return [
            'fichier' => [
                'required',
                'file',
                'max:'.$maxKo,
                function (string $attribute, mixed $value, Closure $fail) use ($mimesAutorises, $extensions, $mimeParExtension): void {
                    if (! $value || ! $value->isValid()) {
                        return;
                    }

                    $nomOriginal = (string) $value->getClientOriginalName();
                    if (str_contains($nomOriginal, '..')
                        || str_contains($nomOriginal, '/')
                        || str_contains($nomOriginal, '\\')) {
                        $fail(__('Nom de fichier invalide.'));

                        return;
                    }

                    $ext = strtolower((string) $value->getClientOriginalExtension());
                    if ($ext === '' || ! in_array($ext, $extensions, true)) {
                        $fail(__('Extension non autorisée.'));

                        return;
                    }

                    $mime = (string) $value->getMimeType();
                    if (! in_array($mime, $mimesAutorises, true)) {
                        $fail(__('Type de fichier non autorisé.'));

                        return;
                    }

                    $attendus = $mimeParExtension[$ext] ?? [];
                    if ($attendus !== [] && ! in_array($mime, $attendus, true)) {
                        $fail(__('Le type du fichier ne correspond pas à l’extension.'));

                        return;
                    }
                },
            ],
        ];
    }
}
