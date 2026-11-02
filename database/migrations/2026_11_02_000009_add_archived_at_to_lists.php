<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Une liste archivée est mise de côté : on la garde, et on ne peut la supprimer que depuis les archives.
    public function up(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};
