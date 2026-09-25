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
        if (!Schema::hasTable('profile_bases')) {
            Schema::create('profile_bases', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('level')->unique(); // 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10...
                $table->string('name'); // e.g. "Level 1 - Bronze Star"
                $table->unsignedBigInteger('required_coins')->default(0); // Lifetime coins threshold (earning/spending)
                $table->string('base_frame_image')->nullable(); // Overlay SVG/PNG avatar border frame
                $table->string('badge_icon')->nullable()->default('star'); // crown, gem, star, fire, dragon, bolt, shield
                $table->string('badge_color')->default('#f59e0b'); // Hex or CSS gradient for badge/border
                $table->string('glow_color')->nullable()->default('rgba(245, 158, 11, 0.45)');
                $table->string('privilege_text')->nullable(); // e.g. "Exclusive animated avatar frame & chat glow"
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_bases');
    }
};
