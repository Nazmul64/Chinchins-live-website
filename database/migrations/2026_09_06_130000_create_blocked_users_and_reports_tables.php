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
        // 1. Blocked Users Table (Peer-to-peer user blocking)
        if (!Schema::hasTable('blocked_users')) {
            Schema::create('blocked_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Who blocked
                $table->foreignId('blocked_user_id')->constrained('users')->onDelete('cascade'); // Who is blocked
                $table->string('reason')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'blocked_user_id']);
                $table->index(['user_id', 'blocked_user_id']);
            });
        }

        // 2. User Reports Table (In-Chat / Profile Reporting)
        if (!Schema::hasTable('user_reports')) {
            Schema::create('user_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('reported_user_id')->constrained('users')->onDelete('cascade');
                $table->string('reason_type', 50); // child_abuse, harassment, sexual_content, unreasonable_demands, scam, other
                $table->string('reason_title', 150)->nullable();
                $table->text('description')->nullable();
                $table->string('proof_image')->nullable();
                $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
                $table->text('admin_notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['reported_user_id', 'status']);
                $table->index(['reporter_id']);
            });
        }

        // 3. Ensure users table has country, age, gender, introduction and bio defaults
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'country_flag')) {
                $table->string('country_flag', 20)->nullable()->default('🇧🇩')->after('country');
            }
            if (!Schema::hasColumn('users', 'charm_level')) {
                $table->unsignedInteger('charm_level')->default(1)->after('level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
        Schema::dropIfExists('blocked_users');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'country_flag')) {
                $table->dropColumn('country_flag');
            }
            if (Schema::hasColumn('users', 'charm_level')) {
                $table->dropColumn('charm_level');
            }
        });
    }
};
