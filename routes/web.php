<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'accueil')->name('accueil');

// « login » et « password.reset » : noms attendus par Laravel (redirection des invités, lien du mail).
Route::middleware('guest')->group(function () {
    Route::view('/inscription', 'auth.inscription')->name('inscription');
    Route::post('/inscription', [AuthController::class, 'inscription'])->middleware('throttle:6,1');
    Route::view('/connexion', 'auth.connexion')->name('login');
    Route::post('/connexion', [AuthController::class, 'connexion'])->middleware('throttle:6,1');
    Route::view('/mot-de-passe-oublie', 'auth.oubli')->name('password.request');
    Route::post('/mot-de-passe-oublie', [AuthController::class, 'envoyerLien'])->middleware('throttle:3,1');
    Route::get('/reinitialiser/{token}', fn (string $token) => view('auth.reinitialiser', ['token' => $token]))->name('password.reset');
    Route::post('/reinitialiser', [AuthController::class, 'reinitialiser'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::view('/mes-listes', 'listes.index')->name('listes');
    Route::post('/deconnexion', [AuthController::class, 'deconnexion'])->name('deconnexion');
});

// Pages pas encore construites : une page « bientôt » en attendant.
Route::view('/idees', 'bientot', ['titre' => 'Idées de cadeaux'])->name('idees');
Route::view('/secret-santa', 'bientot', ['titre' => 'Secret Santa'])->name('secret-santa');
Route::view('/jeux', 'bientot', ['titre' => 'Jeux du jour'])->name('jeux');
