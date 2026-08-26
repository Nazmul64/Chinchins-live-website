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
        // 1. Add coins column to users table if not exists
        if (!Schema::hasColumn('users', 'coins')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('coins')->default(0)->after('video_call_rate');
            });
        }

        // 2. Payment Methods Table (bKash, Nagad, Rocket, Bank, etc.)
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. 'bKash Personal', 'Nagad Agent'
            $table->string('code')->index(); // e.g. 'bkash', 'nagad', 'rocket', 'bank'
            $table->string('account_type')->default('Personal'); // Personal, Agent, Merchant
            $table->string('account_number'); // e.g. '01712345678'
            $table->text('instructions')->nullable(); // e.g. 'Send Money to this number and enter TrxID'
            $table->string('icon')->nullable(); // Logo / icon image URL or path
            $table->string('qr_code')->nullable(); // QR code image URL or path
            $table->decimal('min_deposit', 10, 2)->default(50.00);
            $table->decimal('max_deposit', 10, 2)->default(50000.00);
            $table->unsignedInteger('rate_per_bdt')->default(10); // e.g. 1 BDT = 10 Coins (So 100 BDT = 1000 Coins)
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Deposit Requests Table
        Schema::create('deposit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_method_name')->nullable(); // Snapshot of method name (e.g. bKash)
            $table->decimal('amount', 10, 2); // Amount in BDT
            $table->unsignedBigInteger('coins'); // Coins to receive
            $table->string('sender_number'); // Number money was sent from
            $table->string('transaction_id')->index(); // TrxID
            $table->string('screenshot')->nullable(); // Path to receipt image
            $table->text('user_note')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->text('admin_note')->nullable(); // Optional reason or admin remark
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // 4. Coin Transactions / Ledger Table
        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', [
                'deposit',
                'admin_add',
                'admin_deduct',
                'video_call_spent',
                'video_call_earned',
                'gift_sent',
                'gift_received',
                'refund'
            ])->index();
            $table->bigInteger('amount'); // Positive or Negative change
            $table->unsignedBigInteger('balance_after'); // Running balance after transaction
            $table->string('description')->nullable();
            $table->string('reference_id')->nullable()->index(); // e.g. deposit ID or call session ID
            $table->timestamps();
        });

        // 5. Video Call Sessions Table (Call rate: 100 coins/min)
        Schema::create('call_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->string('channel_name')->unique();
            $table->enum('status', ['initiated', 'connected', 'ended', 'rejected', 'missed', 'failed'])->default('initiated');
            $table->unsignedInteger('rate_per_minute')->default(100); // 100 coins per minute
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedBigInteger('coins_deducted')->default(0);
            $table->unsignedBigInteger('caller_balance_after')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_sessions');
        Schema::dropIfExists('coin_transactions');
        Schema::dropIfExists('deposit_requests');
        Schema::dropIfExists('payment_methods');

        if (Schema::hasColumn('users', 'coins')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('coins');
            });
        }
    }
};
