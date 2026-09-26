<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Party Tags Table
        if (!Schema::hasTable('party_tags')) {
            Schema::create('party_tags', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 100)->unique();
                $table->string('icon', 100)->nullable();
                $table->string('color', 30)->default('#ec4899');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });

            // Seed default dynamic tags
            $defaultTags = [
                ['name' => 'Singing', 'slug' => 'singing', 'icon' => '🎤', 'color' => '#ec4899', 'sort_order' => 1],
                ['name' => 'Gaming', 'slug' => 'gaming', 'icon' => '🎮', 'color' => '#8b5cf6', 'sort_order' => 2],
                ['name' => 'Dating', 'slug' => 'dating', 'icon' => '❤️', 'color' => '#ef4444', 'sort_order' => 3],
                ['name' => 'Chat', 'slug' => 'chat', 'icon' => '💬', 'color' => '#3b82f6', 'sort_order' => 4],
                ['name' => 'Music', 'slug' => 'music', 'icon' => '🎵', 'color' => '#10b981', 'sort_order' => 5],
                ['name' => 'Friends', 'slug' => 'friends', 'icon' => '👥', 'color' => '#f59e0b', 'sort_order' => 6],
                ['name' => 'Poetry', 'slug' => 'poetry', 'icon' => '📖', 'color' => '#06b6d4', 'sort_order' => 7],
                ['name' => 'Hangout', 'slug' => 'hangout', 'icon' => '✨', 'color' => '#6366f1', 'sort_order' => 8],
            ];

            foreach ($defaultTags as $tag) {
                DB::table('party_tags')->insert(array_merge($tag, [
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 2. Add is_held column to withdraw_requests table if not present
        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('withdraw_requests', 'is_held')) {
                    $table->boolean('is_held')->default(false)->after('status')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('party_tags');

        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                if (Schema::hasColumn('withdraw_requests', 'is_held')) {
                    $table->dropColumn('is_held');
                }
            });
        }
    }
};
