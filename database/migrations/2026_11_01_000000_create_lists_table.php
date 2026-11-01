<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lists', function (Blueprint $table) {
            $table->id();
            $table->string('title', 80);
            $table->string('event_type', 20);
            $table->date('event_date')->nullable();
            // Jeton du lien secret : jamais l'id, pour qu'une liste ne se devine pas.
            $table->string('share_token', 40)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lists');
    }
};
