<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Objets des jeux du jour : une photo libre, un prix exact, et le crédit de la photo.
        Schema::create('daily_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->decimal('price', 8, 2);
            $table->string('image', 120);
            $table->string('credit', 160)->nullable();
        });

        // Une partie par visiteur, par jeu et par jour. « guesses » garde les essais, « won » le résultat.
        Schema::create('daily_plays', function (Blueprint $table) {
            $table->id();
            $table->string('visitor', 40);
            $table->string('game', 20);
            $table->date('played_on');
            $table->json('guesses')->nullable();
            $table->boolean('won')->default(false);
            $table->timestamps();
            $table->unique(['visitor', 'game', 'played_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_plays');
        Schema::dropIfExists('daily_items');
    }
};
