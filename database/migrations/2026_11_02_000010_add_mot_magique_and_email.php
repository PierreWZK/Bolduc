<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Le mot magique (haché) permet de se retrouver depuis un autre appareil, sans compte.
    // shortcut: pas d'index unique sur les prénoms (SQLite compare la casse), le contrôle est fait en PHP ; à ajouter si des doublons apparaissent.
    public function up(): void
    {
        Schema::table('santa_members', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->string('magic_word')->nullable();
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->string('magic_word')->nullable();
            // Rempli quand l'invité est connecté : la liste apparaît dans ses « Listes des autres ».
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('magic_word');
        });

        Schema::table('santa_members', function (Blueprint $table) {
            $table->dropColumn(['email', 'magic_word']);
        });
    }
};
