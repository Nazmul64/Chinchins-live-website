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
        if (!Schema::hasTable('coin_packages')) {
            Schema::create('coin_packages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('coins'); // Base Coins / Gems e.g. 32000
                $table->unsignedBigInteger('bonus_coins')->default(0); // Bonus Coins e.g. 8000
                $table->decimal('price', 10, 2); // Price in BDT e.g. 550.00
                $table->string('badge')->nullable(); // e.g. "50% OFF", "Best Value", "+30% Free", "VIP Bonus"
                $table->string('badge_color')->nullable()->default('pink'); // e.g. pink, warning, success, primary
                $table->boolean('is_popular')->default(false)->index();
                $table->boolean('is_active')->default(true)->index();
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
        Schema::dropIfExists('coin_packages');
    }
};
