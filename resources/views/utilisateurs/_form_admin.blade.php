{{--
    Création / édition par super admin ou admin client (phase 4).
    Variables : $utilisateur (model, existant en édition), $clients (collection pour super admin), $mode 'create'|'edit'
--}}
@php
    use App\Enums\RoleUtilisateur;
    $u = $utilisateur;
    $isSuperAdmin = auth()->user()->estSuperAdmin();
    $isClientAdmin = auth()->user()->role === RoleUtilisateur::ClientAdmin;
@endphp

<div style="display: grid; gap: 1rem;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div>
            <label for="prenom" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Prénom *</label>
            <input type="text" id="prenom" name="prenom" required maxlength="255" value="{{ old('prenom', $u->prenom) }}"
                style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">
            @error('prenom')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="nom" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Nom *</label>
            <input type="text" id="nom" name="nom" required maxlength="255" value="{{ old('nom', $u->nom) }}"
                style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">
            @error('nom')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
        </div>
    </div>
    <div>
        <label for="email" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">E-mail *</label>
        <input type="email" id="email" name="email" required maxlength="191" value="{{ old('email', $u->email) }}"
            style="width: 100%; max-width: 28rem; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">
        @error('email')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
    </div>

    @if($mode === 'create')
        <div>
            <label for="password" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Mot de passe * (min. 8 caractères)</label>
            <input type="password" id="password" name="password" required autocomplete="new-password"
                style="width: 100%; max-width: 28rem; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">
            @error('password')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Confirmer le mot de passe *</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                style="width: 100%; max-width: 28rem; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">
        </div>
    @else
        <div>
            <label for="password" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" id="password" name="password" autocomplete="new-password"
                style="width: 100%; max-width: 28rem; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">
            @error('password')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Confirmation</label>
            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                style="width: 100%; max-width: 28rem; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">
        </div>
    @endif

    <div>
        <label for="role" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Rôle *</label>
        <select id="role" name="role" required style="padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; max-width: 28rem;">
            @if($mode === 'create')
                <option value="" disabled @selected(old('role', $u->role?->value) === null)>— Choisir un rôle —</option>
            @endif
            @if($isSuperAdmin)
                <option value="{{ RoleUtilisateur::SuperAdmin->value }}" @selected(old('role', $u->role?->value) === RoleUtilisateur::SuperAdmin->value)>Super administrateur</option>
                <option value="{{ RoleUtilisateur::ClientAdmin->value }}" @selected(old('role', $u->role?->value) === RoleUtilisateur::ClientAdmin->value)>Administrateur client</option>
                <option value="{{ RoleUtilisateur::ClientUser->value }}" @selected(old('role', $u->role?->value) === RoleUtilisateur::ClientUser->value)>Utilisateur client</option>
                <option value="{{ RoleUtilisateur::Technicien->value }}" @selected(old('role', $u->role?->value) === RoleUtilisateur::Technicien->value)>Technicien</option>
            @else
                <option value="{{ RoleUtilisateur::ClientAdmin->value }}" @selected(old('role', $u->role?->value) === RoleUtilisateur::ClientAdmin->value)>Administrateur client</option>
                <option value="{{ RoleUtilisateur::ClientUser->value }}" @selected(old('role', $u->role?->value) === RoleUtilisateur::ClientUser->value)>Utilisateur client</option>
            @endif
        </select>
        @error('role')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="statut" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Statut compte *</label>
        <select id="statut" name="statut" required style="padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem;">
            <option value="actif" @selected(old('statut', $u->statut) === 'actif')>Actif</option>
            <option value="inactif" @selected(old('statut', $u->statut) === 'inactif')>Inactif</option>
        </select>
        @error('statut')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
    </div>

    @if($isSuperAdmin)
        <div>
            <label for="client_id" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.25rem;">Entreprise cliente</label>
            <select id="client_id" name="client_id" style="padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; max-width: 28rem;">
                <option value="">— Aucune (super admin / technicien) —</option>
                @foreach($clients as $cl)
                    <option value="{{ $cl->id }}" @selected(old('client_id', $u->client_id) == $cl->id)>{{ $cl->nom_entreprise }}</option>
                @endforeach
            </select>
            <p style="font-size: 0.8125rem; color: #64748b; margin: 0.35rem 0 0;">Obligatoire pour les rôles « administrateur / utilisateur client » ; vide pour technicien ou super admin.</p>
            @error('client_id')<p style="color: #b91c1c; font-size: 0.875rem; margin: 0.25rem 0 0;">{{ $message }}</p>@enderror
        </div>
    @endif
</div>
