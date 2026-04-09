<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Journalisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Connexion session (guard `web`, provider `utilisateurs`).
 *
 * - Mot de passe : hash géré par le cast `hashed` sur le modèle.
 * - Compte inactif : refus via Auth::attemptWhen (pas de session créée).
 */
class LoginController extends Controller
{
    /** Formulaire de connexion (invités uniquement, voir routes). */
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        // attemptWhen : vérifie le mot de passe puis exécute la closure.
        // Retour false si mauvais mot de passe OU si le compte n’est pas actif.
        $ok = Auth::attemptWhen(
            [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ],
            fn ($user) => $user->estActif(),
            $remember
        );

        if (! $ok) {
            Journalisation::traceSansUtilisateur(
                $request->ip(),
                'connexion_echouee',
                'Identifiants invalides ou compte inactif : '.($credentials['email'] ?? '')
            );
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();

        Journalisation::trace(
            $request,
            'connexion_reussie',
            'Connexion : '.Auth::user()->email
        );

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        if ($request->user()) {
            Journalisation::trace(
                $request,
                'deconnexion',
                'Déconnexion : '.$request->user()->email
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
