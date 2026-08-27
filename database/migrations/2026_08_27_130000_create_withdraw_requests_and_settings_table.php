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
        // 1. Withdrawal Settings Table
        if (!Schema::hasTable('withdrawal_settings')) {
            Schema::create('withdrawal_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('withdrawal');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // 2. Add withdrawal support fields to payment_methods table if not present
        Schema::table('payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_methods', 'supports_withdraw')) {
                $table->boolean('supports_withdraw')->default(true)->after('is_active');
            }
            if (!Schema::hasColumn('payment_methods', 'min_withdraw')) {
                $table->decimal('min_withdraw', 10, 2)->default(50.00)->after('supports_withdraw');
            }
            if (!Schema::hasColumn('payment_methods', 'max_withdraw')) {
                $table->decimal('max_withdraw', 10, 2)->default(50000.00)->after('min_withdraw');
            }
        });

        // 3. Withdraw Requests Table
        if (!Schema::hasTable('withdraw_requests')) {
            Schema::create('withdraw_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
                $table->string('payment_method_name')->nullable(); // e.g. 'bKash Personal', 'Nagad Personal'
                $table->unsignedBigInteger('coins'); // Amount of coins requested to withdraw
                $table->decimal('rate_per_bdt', 10, 2)->default(10.00); // Snapshot: coins per 1 BDT (e.g. 10 coins = 1 BDT)
                $table->decimal('gross_amount', 10, 2); // Gross BDT amount before commission
                $table->decimal('commission_percent', 5, 2)->default(0.00); // Commission fee percentage (e.g. 5.00%)
                $table->decimal('commission_amount', 10, 2)->default(0.00); // Deducted commission in BDT
                $table->decimal('net_payable_amount', 10, 2); // Net BDT user will actually receive
                $table->string('account_number'); // User's bKash/Nagad/Rocket phone number or bank account
                $table->string('account_type')->default('Personal'); // Personal, Agent, Merchant
                $table->text('user_note')->nullable(); // Optional note from user
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
                $table->string('transaction_id')->nullable(); // TrxID or payout reference entered by admin upon payment
                $table->text('admin_note')->nullable(); // Admin remarks or rejection reason
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Update coin_transactions type column to string to allow 'withdraw' without enum restriction
        if (Schema::hasTable('coin_transactions')) {
            Schema::table('coin_transactions', function (Blueprint $table) {
                $table->string('type', 50)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
        Schema::dropIfExists('withdrawal_settings');

        if (Schema::hasTable('payment_methods')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                if (Schema::hasColumn('payment_methods', 'supports_withdraw')) {
                    $table->dropColumn('supports_withdraw');
                }
                if (Schema::hasColumn('payment_methods', 'min_withdraw')) {
                    $table->dropColumn('min_withdraw');
                }
                if (Schema::hasColumn('payment_methods', 'max_withdraw')) {
                    $table->dropColumn('max_withdraw');
                }
            });
        }
    }
};
