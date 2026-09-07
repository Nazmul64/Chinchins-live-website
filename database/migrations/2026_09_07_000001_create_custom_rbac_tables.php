<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Custom Role & Permission Management System.
     */
    public function up(): void
    {
        // 1. Roles table
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 100)->unique();
                $table->text('description')->nullable();
                $table->string('status', 30)->default('active'); // active | inactive
                $table->boolean('is_default')->default(false); // protected default system roles
                $table->timestamps();
            });
        }

        // 2. Permissions table
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('module', 100); // e.g. deposits, users, kyc
                $table->string('name', 150); // e.g. Approve Deposit
                $table->string('slug', 150)->unique(); // e.g. deposits.approve
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 3. Role Permissions Pivot table
        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
                $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['role_id', 'permission_id']);
            });
        }

        // 4. User Specific Permission Overrides table (allow or deny)
        if (!Schema::hasTable('user_permissions')) {
            Schema::create('user_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
                $table->string('type', 20)->default('allow'); // 'allow' or 'deny'
                $table->timestamps();

                $table->unique(['user_id', 'permission_id']);
            });
        }

        // 5. Activity Logs Audit Trail table
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('user_name', 150)->nullable();
                $table->string('user_role', 100)->nullable();
                $table->string('module', 100); // e.g. deposits, users, roles, auth
                $table->string('action', 100); // e.g. approved, rejected, created, updated, deleted, login
                $table->text('description');
                $table->json('old_data')->nullable();
                $table->json('new_data')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['module', 'action']);
                $table->index('created_at');
            });
        }

        // 6. Staff Login History table
        if (!Schema::hasTable('login_histories')) {
            Schema::create('login_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->string('user_name', 150)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('role_name', 100)->nullable();
                $table->timestamp('login_at')->nullable();
                $table->timestamp('logout_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('browser', 100)->nullable();
                $table->string('device', 100)->nullable();
                $table->string('status', 30)->default('success'); // success | failed | locked
                $table->string('failure_reason', 255)->nullable();
                $table->timestamps();

                $table->index('login_at');
            });
        }

        // 7. Extend Users table with RBAC & status columns
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->after('password')->constrained('roles')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 30)->default('active')->after('role_id'); // active | inactive | suspended
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->unsignedInteger('failed_login_attempts')->default(0)->after('last_login_at');
            }
            if (!Schema::hasColumn('users', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropForeign(['role_id']);
                $table->dropColumn(['role_id', 'status', 'last_login_at', 'failed_login_attempts', 'locked_until']);
            }
        });

        Schema::dropIfExists('login_histories');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
