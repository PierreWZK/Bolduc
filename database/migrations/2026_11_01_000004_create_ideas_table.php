<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catalogue rédigé par nous (graines), lu par /idees. Prix indicatif, jamais vérifié en boutique.
        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120)->unique();
            $table->string('description', 240);
            $table->string('category', 30);
            $table->string('icon', 30);
            $table->decimal('price', 8, 2);
            $table->json('events');
            $table->timestamps();
        });

        Schema::table('gifts', function (Blueprint $table) {
            // Idée ajoutée en secret par un invité : cachée du propriétaire, visible des autres invités.
            $table->boolean('secret')->default(false);
            $table->foreignId('added_by')->nullable()->constrained('guests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gifts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('added_by');
            $table->dropColumn('secret');
        });
        Schema::dropIfExists('ideas');
    }
};
