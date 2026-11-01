<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Indices écrits par celui qui offre, pour sa cible : emoji, surnom, phrase, puis trois indices du cadeau.
        Schema::create('soiree_indices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('santa_members')->cascadeOnDelete();
            $table->string('kind', 20);
            $table->string('value', 120);
            $table->unique(['member_id', 'kind']);
        });

        // Une manche = un joueur dans un jeu. « qui » : il devine son donneur. « cadeau » : il devine ce qu'il reçoit.
        Schema::create('soiree_manches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('santa_members')->cascadeOnDelete();
            $table->string('game', 10);
            $table->unsignedTinyInteger('revealed')->default(1);
            $table->boolean('solved')->default(false);
            $table->unsignedTinyInteger('points')->default(0);
            $table->string('guess', 120)->nullable();
            $table->timestamps();
            $table->unique(['member_id', 'game']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soiree_manches');
        Schema::dropIfExists('soiree_indices');
    }
};
