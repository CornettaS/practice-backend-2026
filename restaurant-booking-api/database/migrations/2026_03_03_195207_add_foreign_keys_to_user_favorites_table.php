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
        Schema::table('user_favorites', function (Blueprint $table) {
            $table->foreign(['user_id'], 'user_favorites_ibfk_1')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['restaurant_id'], 'user_favorites_ibfk_2')->references(['id'])->on('restaurants')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_favorites', function (Blueprint $table) {
            $table->dropForeign('user_favorites_ibfk_1');
            $table->dropForeign('user_favorites_ibfk_2');
        });
    }
};
