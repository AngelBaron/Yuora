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
        //hace nulo la columa title y description

        Schema::table('post_media', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->string('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //vuelve a hacer no nulo la columa title y description

        Schema::table('post_media', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->string('description')->nullable(false)->change();
        });
    }
};
