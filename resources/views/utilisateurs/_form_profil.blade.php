{{-- Édition profil par un utilisateur client simple (sans gestion des rôles). --}}
@php $u = $utilisateur; @endphp
<div style="display: grid; gap: 1rem;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div>
            <label for="prenom" class="lbl">Prénom *</label>
            <input type="text" id="prenom" name="prenom" required maxlength="255" value="{{ old('prenom', $u->prenom) }}" class="inp">
            @error('prenom')<p class="err">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="nom" class="lbl">Nom *</label>
            <input type="text" id="nom" name="nom" required maxlength="255" value="{{ old('nom', $u->nom) }}" class="inp">
            @error('nom')<p class="err">{{ $message }}</p>@enderror
        </div>
    </div>
    <div>
        <label for="email" class="lbl">E-mail *</label>
        <input type="email" id="email" name="email" required maxlength="191" value="{{ old('email', $u->email) }}" class="inp" style="max-width: 28rem;">
        @error('email')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password" class="lbl">Nouveau mot de passe (optionnel)</label>
        <input type="password" id="password" name="password" class="inp" style="max-width: 28rem;" autocomplete="new-password">
        @error('password')<p class="err">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password_confirmation" class="lbl">Confirmation</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="inp" style="max-width: 28rem;" autocomplete="new-password">
    </div>
</div>
<style>.lbl{display:block;font-size:0.875rem;font-weight:600;margin-bottom:0.25rem}.inp{width:100%;padding:0.5rem;border:1px solid #cbd5e1;border-radius:0.375rem;box-sizing:border-box}.err{color:#b91c1c;font-size:0.875rem;margin:0.25rem 0 0}</style>
