<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CadeauController;
use App\Http\Controllers\IdeeController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\ListeController;
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
    Route::get('/mes-listes', [ListeController::class, 'index'])->name('listes');
    Route::get('/mes-listes/nouvelle', [ListeController::class, 'create'])->name('listes.create');
    Route::post('/mes-listes', [ListeController::class, 'store'])->name('listes.store');
    Route::get('/mes-listes/{liste}', [ListeController::class, 'show'])->name('listes.show');
    Route::post('/idees/{idee}/ajouter', [IdeeController::class, 'ajouter'])->name('idees.ajouter');
    Route::post('/mes-listes/{liste}/cadeaux', [CadeauController::class, 'store'])->name('cadeaux.store');
    Route::delete('/mes-listes/{liste}/cadeaux/{cadeau}', [CadeauController::class, 'destroy'])->name('cadeaux.destroy');
    Route::post('/deconnexion', [AuthController::class, 'deconnexion'])->name('deconnexion');
});

// Lien secret d'une liste, ouvert par les invités sans compte.
Route::get('/l/{token}', [InviteController::class, 'show'])->name('invite');
Route::post('/l/{token}/prenom', [InviteController::class, 'prenom'])->middleware('throttle:10,1')->name('invite.prenom');

Route::get('/idees', [IdeeController::class, 'index'])->name('idees');
Route::get('/l/{token}/idees', [IdeeController::class, 'index'])->name('invite.idees');
Route::post('/l/{token}/idees/{idee}', [IdeeController::class, 'ajouterEnSecret'])->name('invite.idees.ajouter');

// Pages pas encore construites : une page « bientôt » en attendant.
Route::view('/secret-santa', 'bientot', ['titre' => 'Secret Santa'])->name('secret-santa');
Route::view('/jeux', 'bientot', ['titre' => 'Jeux du jour'])->name('jeux');
