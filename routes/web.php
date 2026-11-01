<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'accueil')->name('accueil');

// Pages pas encore construites : une page « bientôt » en attendant.
Route::view('/inscription', 'bientot', ['titre' => 'Créer ma liste'])->name('inscription');
Route::view('/idees', 'bientot', ['titre' => 'Idées de cadeaux'])->name('idees');
Route::view('/secret-santa', 'bientot', ['titre' => 'Secret Santa'])->name('secret-santa');
Route::view('/jeux', 'bientot', ['titre' => 'Jeux du jour'])->name('jeux');
