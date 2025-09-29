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
        Schema::create('drills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Coach who created the drill
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('action_type'); // e.g., 'spike', 'serve', 'block'
            $table->json('criteria')->nullable(); // JSON for specific criteria (e.g., joint angles)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drills');
    }
};
