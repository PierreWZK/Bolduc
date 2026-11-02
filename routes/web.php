<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CadeauController;
use App\Http\Controllers\IdeeController;
use App\Http\Controllers\JeuxController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\SantaController;
use App\Http\Controllers\SoireeController;
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
    Route::get('/mes-listes/{liste}/modifier', [ListeController::class, 'edit'])->name('listes.edit');
    Route::put('/mes-listes/{liste}', [ListeController::class, 'update'])->name('listes.update');
    Route::post('/mes-listes/{liste}/archiver', [ListeController::class, 'archiver'])->name('listes.archiver');
    Route::delete('/mes-listes/{liste}', [ListeController::class, 'destroy'])->name('listes.destroy');
    Route::post('/idees/{idee}/ajouter', [IdeeController::class, 'ajouter'])->name('idees.ajouter');

    Route::get('/secret-santa', [SantaController::class, 'index'])->name('secret-santa');
    Route::post('/secret-santa', [SantaController::class, 'store'])->name('santa.store');
    Route::get('/secret-santa/{groupe}', [SantaController::class, 'show'])->name('santa.show');
    Route::post('/secret-santa/{groupe}/exclusions', [SantaController::class, 'exclure'])->name('santa.exclure');
    Route::post('/secret-santa/{groupe}/tirage', [SantaController::class, 'tirer'])->name('santa.tirer');
    Route::post('/mes-listes/{liste}/cadeaux', [CadeauController::class, 'store'])->name('cadeaux.store');
    Route::delete('/mes-listes/{liste}/cadeaux/{cadeau}', [CadeauController::class, 'destroy'])->name('cadeaux.destroy');
    Route::post('/mes-listes/{liste}/cadeaux/{cadeau}/merci', [CadeauController::class, 'merci'])->name('cadeaux.merci');
    Route::post('/deconnexion', [AuthController::class, 'deconnexion'])->name('deconnexion');
});

// Lien secret d'une liste, ouvert par les invités sans compte.
Route::get('/l/{token}', [InviteController::class, 'show'])->name('invite');
Route::post('/l/{token}/prenom', [InviteController::class, 'prenom'])->middleware('throttle:10,1')->name('invite.prenom');
Route::post('/l/{token}/retrouver', [InviteController::class, 'retrouver'])->middleware('throttle:10,1')->name('invite.retrouver');

// Lien d'invitation d'un Secret Santa : les participants n'ont pas de compte.
Route::get('/s/{token}', [SantaController::class, 'invite'])->name('santa.invite');
Route::post('/s/{token}', [SantaController::class, 'rejoindre'])->middleware('throttle:10,1')->name('santa.rejoindre');
Route::post('/s/{token}/retrouver', [SantaController::class, 'retrouver'])->middleware('throttle:10,1')->name('santa.retrouver');

// Mode soirée : les indices (écrits par le donneur) et les deux jeux, une fois le tirage fait.
Route::get('/s/{token}/indices', [SoireeController::class, 'indices'])->name('santa.indices');
Route::post('/s/{token}/indices', [SoireeController::class, 'sauverIndices'])->name('santa.indices.sauver');
Route::get('/s/{token}/soiree', [SoireeController::class, 'soiree'])->name('santa.soiree');

Route::get('/idees', [IdeeController::class, 'index'])->name('idees');
Route::get('/l/{token}/idees', [IdeeController::class, 'index'])->name('invite.idees');
Route::post('/l/{token}/idees/{idee}', [IdeeController::class, 'ajouterEnSecret'])->name('invite.idees.ajouter');

// Seules les pages publiques sont dans le plan du site ; les listes et le Secret Santa ne sont jamais indexés.
Route::get('/sitemap.xml', fn () => response()
    ->view('sitemap', ['pages' => [route('accueil'), route('idees')]])
    ->header('Content-Type', 'application/xml'))->name('sitemap');

// Jeux du jour : un objet par jour, le même pour tout le monde.
Route::get('/jeux', [JeuxController::class, 'index'])->name('jeux');
