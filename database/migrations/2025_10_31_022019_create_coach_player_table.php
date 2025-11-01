<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_player', function (Blueprint $table) {
            $table->id();

            // ✅ These are the two required columns
            $table->unsignedBigInteger('coach_id');
            $table->unsignedBigInteger('player_id');

            $table->foreign('coach_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('player_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_player');
    }
};
