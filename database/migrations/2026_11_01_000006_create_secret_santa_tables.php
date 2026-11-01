<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santa_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 80);
            // Lien d'invitation : jeton long, jamais l'id.
            $table->string('share_token', 40)->unique();
            $table->timestamp('drawn_at')->nullable();
            $table->timestamps();
        });

        Schema::create('santa_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('santa_groups')->cascadeOnDelete();
            $table->string('name', 40);
            $table->string('wishes', 240)->nullable();
            // Jeton gardé en cookie par le participant : il sert à retrouver sa cible.
            $table->string('token', 40)->unique();
            $table->foreignId('target_id')->nullable()->constrained('santa_members')->nullOnDelete();
            $table->timestamps();
        });

        // Une ligne par sens : A ne tire pas B, et B ne tire pas A.
        Schema::create('santa_exclusions', function (Blueprint $table) {
            $table->foreignId('member_id')->constrained('santa_members')->cascadeOnDelete();
            $table->foreignId('excluded_id')->constrained('santa_members')->cascadeOnDelete();
            $table->primary(['member_id', 'excluded_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santa_exclusions');
        Schema::dropIfExists('santa_members');
        Schema::dropIfExists('santa_groups');
    }
};
