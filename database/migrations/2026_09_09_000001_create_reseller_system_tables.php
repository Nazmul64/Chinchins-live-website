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
        // 1. Resellers Table
        if (!Schema::hasTable('resellers')) {
            Schema::create('resellers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('phone')->nullable();
                $table->string('avatar')->nullable();
                $table->string('level')->default('Lv1');
                $table->string('location')->default('Dhaka, Bangladesh');
                $table->integer('age')->default(25);
                $table->string('gender')->default('male');
                $table->text('bio')->nullable();
                $table->string('discount_tag')->default('Up To 29%↑');
                $table->string('badge_title')->default('Diamond Reseller');
                $table->unsignedBigInteger('coins_balance')->default(0);
                $table->unsignedBigInteger('total_sold_coins')->default(0);
                $table->unsignedBigInteger('total_deposited_coins')->default(0);
                $table->unsignedBigInteger('total_withdrawn_coins')->default(0);
                $table->decimal('commission_rate', 5, 2)->default(0.00);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_online')->default(false);
                $table->timestamp('last_seen_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // 2. Reseller Coin Transfers to Users
        if (!Schema::hasTable('reseller_transfers')) {
            Schema::create('reseller_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('target_account_id')->index();
                $table->unsignedBigInteger('coins');
                $table->decimal('amount_bdt', 12, 2)->nullable();
                $table->string('payment_method')->nullable();
                $table->string('transaction_id')->nullable();
                $table->string('screenshot')->nullable();
                $table->text('notes')->nullable();
                $table->enum('status', ['completed', 'cancelled', 'refunded'])->default('completed');
                $table->timestamps();
            });
        }

        // 3. Reseller Deposits from Admin
        if (!Schema::hasTable('reseller_deposits')) {
            Schema::create('reseller_deposits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
                $table->string('payment_method')->default('bKash');
                $table->string('sender_number')->nullable();
                $table->string('transaction_id')->nullable();
                $table->decimal('amount_bdt', 12, 2);
                $table->unsignedBigInteger('coins_requested');
                $table->string('screenshot')->nullable();
                $table->text('notes')->nullable();
                $table->text('admin_notes')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamps();
            });
        }

        // 4. Reseller Withdrawals from Admin
        if (!Schema::hasTable('reseller_withdrawals')) {
            Schema::create('reseller_withdrawals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
                $table->string('payment_method')->default('bKash');
                $table->string('account_number');
                $table->string('account_name')->nullable();
                $table->unsignedBigInteger('coins_amount');
                $table->decimal('gross_bdt', 12, 2);
                $table->decimal('commission_percentage', 5, 2)->default(0.00);
                $table->decimal('commission_amount', 12, 2)->default(0.00);
                $table->decimal('net_bdt', 12, 2);
                $table->string('transaction_id')->nullable();
                $table->text('reseller_notes')->nullable();
                $table->text('admin_notes')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->timestamp('processed_at')->nullable();
                $table->unsignedBigInteger('processed_by')->nullable();
                $table->timestamps();
            });
        }

        // 5. Reseller Chat Messages (User <-> Reseller & Reseller <-> Admin)
        if (!Schema::hasTable('reseller_chat_messages')) {
            Schema::create('reseller_chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('sender_type', ['user', 'reseller', 'admin'])->default('user');
                $table->unsignedBigInteger('sender_id')->nullable();
                $table->enum('type', ['text', 'image', 'voice', 'system', 'recharge_request', 'gift'])->default('text');
                $table->text('message')->nullable();
                $table->string('media_url')->nullable();
                $table->integer('duration')->nullable(); // For voice notes
                $table->unsignedBigInteger('coins_amount')->nullable();
                $table->decimal('amount_bdt', 12, 2)->nullable();
                $table->string('target_account_id')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // 6. Reseller System Settings
        if (!Schema::hasTable('reseller_settings')) {
            Schema::create('reseller_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reseller_chat_messages');
        Schema::dropIfExists('reseller_withdrawals');
        Schema::dropIfExists('reseller_deposits');
        Schema::dropIfExists('reseller_transfers');
        Schema::dropIfExists('reseller_settings');
        Schema::dropIfExists('resellers');
    }
};
