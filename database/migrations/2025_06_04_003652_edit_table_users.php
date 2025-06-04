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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_artist')->default('0')->after('password');
            $table->string('perfil_photo')->nullable()->after('is_artist');
            $table->string('cover_photo')->nullable()->after('perfil_photo');
            $table->string('display_preference')->default('default')->after('cover_photo');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_artist', 'perfil_photo', 'cover_photo', 'display_preference']);
        });
    }
};
