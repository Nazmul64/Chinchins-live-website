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
        if (!Schema::hasTable('user_admin_support_messages')) {
            Schema::create('user_admin_support_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('admin_id')->nullable()->index();
                $table->enum('sender_type', ['user', 'admin'])->default('user');
                $table->enum('type', ['text', 'image', 'voice', 'system'])->default('text');
                $table->text('message')->nullable();
                $table->string('media_url')->nullable();
                $table->boolean('is_read_by_admin')->default(false);
                $table->boolean('is_read_by_user')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_admin_support_messages');
    }
};
