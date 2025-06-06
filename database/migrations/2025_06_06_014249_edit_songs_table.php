<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //cambiar la columna de photo_song a nullable
        Schema::table('songs', function (Blueprint $table) {
            $table->string('photo_song')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('songs', function (Blueprint $table) {
            $table->string('photo_song')->nullable(false)->change();
        });
    }
};
