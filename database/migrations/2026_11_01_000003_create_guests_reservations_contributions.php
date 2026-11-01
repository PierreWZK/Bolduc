<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gifts', function (Blueprint $table) {
            $table->boolean('shared')->default(false);
        });

        // Invités sans compte : un prénom, un e-mail facultatif, un jeton gardé en cookie.
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('lists')->cascadeOnDelete();
            $table->string('name', 40);
            $table->string('email')->nullable();
            $table->string('token', 40)->unique();
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_id')->constrained('gifts')->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->timestamps();
            $table->unique(['gift_id', 'guest_id']);
        });

        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_id')->constrained('gifts')->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
            $table->decimal('amount', 8, 2);
            $table->timestamps();
            $table->unique(['gift_id', 'guest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributions');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('guests');
        Schema::table('gifts', fn (Blueprint $table) => $table->dropColumn('shared'));
    }
};
