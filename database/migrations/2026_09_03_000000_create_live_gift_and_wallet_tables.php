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
        // 1. Wallets Table (User Balance & Host Earnings)
        if (!Schema::hasTable('wallets')) {
            Schema::create('wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
                $table->unsignedBigInteger('balance')->default(0); // Gift Sender Balance / Recharge coins
                $table->unsignedBigInteger('earnings')->default(0); // Host Withdrawable Balance / Received gifts
                $table->timestamps();
            });
        }

        // 2. Gift Transactions Table (Live stream gift logs)
        if (!Schema::hasTable('gift_transactions')) {
            Schema::create('gift_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('stream_id')->index(); // Live Stream ID or Channel
                $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('gift_id')->constrained('gifts')->onDelete('cascade');
                $table->unsignedInteger('coins_spent');
                $table->timestamps();

                $table->index(['stream_id', 'created_at']);
                $table->index(['sender_id', 'receiver_id']);
            });
        }

        // 3. Coin Purchase Logs Table
        if (!Schema::hasTable('coin_purchase_logs')) {
            Schema::create('coin_purchase_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->unsignedBigInteger('package_id')->nullable()->index();
                $table->unsignedBigInteger('coins')->default(0);
                $table->decimal('amount_paid', 10, 2)->default(0.00);
                $table->string('currency', 10)->default('BDT');
                $table->string('payment_method')->nullable();
                $table->timestamps();
            });
        }

        // 4. Enhance Gifts Table with Live Streaming Engine attributes
        Schema::table('gifts', function (Blueprint $table) {
            if (!Schema::hasColumn('gifts', 'coin_price')) {
                $table->unsignedInteger('coin_price')->default(100)->after('coins');
            }
            if (!Schema::hasColumn('gifts', 'icon_url')) {
                $table->string('icon_url')->nullable()->after('image');
            }
            if (!Schema::hasColumn('gifts', 'file_url')) {
                $table->string('file_url')->nullable()->after('animation_url');
            }
            if (!Schema::hasColumn('gifts', 'format')) {
                $table->string('format', 20)->default('svga')->after('animation_type'); // 'svga', 'lottie', 'webp', 'image'
            }
            if (!Schema::hasColumn('gifts', 'display_type')) {
                $table->string('display_type', 30)->default('fullscreen')->after('format'); // 'fullscreen', 'bubble'
            }
        });

        // 5. Enhance Coin Packages Table with title & currency if missing
        Schema::table('coin_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('coin_packages', 'title')) {
                $table->string('title')->nullable()->after('id');
            }
            if (!Schema::hasColumn('coin_packages', 'currency')) {
                $table->string('currency', 10)->default('BDT')->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_purchase_logs');
        Schema::dropIfExists('gift_transactions');
        Schema::dropIfExists('wallets');
    }
};
