<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as RegleMotDePasse;

class AuthController extends Controller
{
    public function inscription(Request $request)
    {
        $request->merge(['email' => Str::lower((string) $request->input('email'))]);
        $donnees = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', RegleMotDePasse::min(8)],
        ], [
            'email.unique' => 'Un compte existe déjà avec cet e-mail. Connecte-toi plutôt.',
        ]);

        Auth::login(User::create($donnees), remember: true);
        $request->session()->regenerate();

        return redirect()->route('listes');
    }

    public function connexion(Request $request)
    {
        $request->merge(['email' => Str::lower((string) $request->input('email'))]);
        $identifiants = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($identifiants, remember: true)) {
            return back()->withErrors(['email' => 'E-mail ou mot de passe incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('listes'));
    }

    public function deconnexion(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('accueil');
    }

    public function envoyerLien(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
        Password::sendResetLink(['email' => Str::lower($request->input('email'))]);

        // Même réponse que le compte existe ou non : on ne révèle pas qui est inscrit.
        return back()->with('statut', 'Si un compte existe avec cet e-mail, un lien vient de partir. Pense à regarder les indésirables.');
    }

    public function reinitialiser(Request $request)
    {
        $request->merge(['email' => Str::lower((string) $request->input('email'))]);
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', RegleMotDePasse::min(8)],
        ]);

        $statut = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $motDePasse) {
                $user->forceFill(['password' => $motDePasse, 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($user));
            }
        );

        if ($statut !== Password::PasswordReset) {
            return back()->withErrors(['email' => 'Ce lien a expiré ou n\'est plus valable. Demandes-en un nouveau.'])->onlyInput('email');
        }

        return redirect()->route('login')->with('statut', 'Mot de passe changé. Tu peux te connecter.');
    }
}
