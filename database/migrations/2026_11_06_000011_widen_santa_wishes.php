<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Les envies deviennent aussi un petit mot pour son Père Noël secret : 240 caractères ne suffisaient plus.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santa_members', function (Blueprint $table) {
            $table->string('wishes', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('santa_members', function (Blueprint $table) {
            $table->string('wishes', 240)->nullable()->change();
        });
    }
};
