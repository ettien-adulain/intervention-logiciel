{{--
    Champs communs création / édition client.
    Variables attendues : $client (nullable en création), optionnellement $mode = 'create'|'edit'
--}}
@php
    $client = $client ?? new \App\Models\Client(['statut' => 'actif']);
@endphp

<div style="display: grid; gap: 1rem;">
    <div>
        <label for="nom_entreprise" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Nom de l’entreprise *</label>
        <input type="text" id="nom_entreprise" name="nom_entreprise" required maxlength="255"
            value="{{ old('nom_entreprise', $client->nom_entreprise) }}"
            style="width: 100%; max-width: 28rem; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">
        @error('nom_entreprise')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="email" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">E-mail</label>
        <input type="email" id="email" name="email" maxlength="255"
            value="{{ old('email', $client->email) }}"
            style="width: 100%; max-width: 28rem; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">
        @error('email')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="telephone" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Téléphone</label>
        <input type="text" id="telephone" name="telephone" maxlength="255"
            value="{{ old('telephone', $client->telephone) }}"
            style="width: 100%; max-width: 28rem; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">
        @error('telephone')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="adresse" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Adresse</label>
        <textarea id="adresse" name="adresse" rows="3"
            style="width: 100%; max-width: 28rem; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">{{ old('adresse', $client->adresse) }}</textarea>
        @error('adresse')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
    </div>

    {{-- Seul le super admin peut activer / désactiver l’entreprise (CDC §4.1). --}}
    @if(auth()->user()->estSuperAdmin())
        <div>
            <label for="statut" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Statut *</label>
            <select id="statut" name="statut" required
                style="padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem;">
                <option value="actif" @selected(old('statut', $client->statut) === 'actif')>Actif</option>
                <option value="inactif" @selected(old('statut', $client->statut) === 'inactif')>Inactif</option>
            </select>
            <p style="font-size: 0.8125rem; color: #64748b; margin: 0.35rem 0 0;">
                Client <strong>inactif</strong> : aucune nouvelle requête ne pourra être créée (règle métier — phase 5).
            </p>
            @error('statut')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
        </div>
    @endif
</div>
