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
        Schema::table('volleyball_sessions', function (Blueprint $table) {
            $table->string('stickman_animation_path')->nullable()->after('keypoints_json_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volleyball_sessions', function (Blueprint $table) {
            $table->dropColumn('stickman_animation_path');
        });
    }
};
