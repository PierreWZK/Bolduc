<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gifts', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('list_id');
        });

        // Les cadeaux existants gardent leur ordre d'ajout.
        DB::table('gifts')->update(['position' => DB::raw('id')]);
    }

    public function down(): void
    {
        Schema::table('gifts', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
