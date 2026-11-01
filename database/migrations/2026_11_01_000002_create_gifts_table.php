<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('lists')->cascadeOnDelete();
            $table->string('title', 120);
            $table->string('url', 2048)->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->boolean('favorite')->default(false);
            $table->string('note', 120)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};
