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
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
        if (!Schema::hasColumn('users', 'deleted_reason')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('deleted_reason')->nullable();
            });
        }
        if (!Schema::hasColumn('users', 'deleted_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('deleted_by', 50)->nullable()->default('admin');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deleted_by')) {
                $table->dropColumn('deleted_by');
            }
            if (Schema::hasColumn('users', 'deleted_reason')) {
                $table->dropColumn('deleted_reason');
            }
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
