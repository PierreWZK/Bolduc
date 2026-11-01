<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            // Date d'envoi du rappel J-7 : évite de le renvoyer si la commande tourne deux fois le même jour.
            $table->timestamp('reminder_sent_at')->nullable();
        });

        Schema::table('gifts', function (Blueprint $table) {
            // Case « merci envoyé », cochée par le propriétaire après l'événement.
            $table->boolean('thanked')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('gifts', fn (Blueprint $table) => $table->dropColumn('thanked'));
        Schema::table('lists', fn (Blueprint $table) => $table->dropColumn('reminder_sent_at'));
    }
};
