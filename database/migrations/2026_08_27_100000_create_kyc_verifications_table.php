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
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Document details: 'nid', 'passport', 'birth_certificate'
            $table->enum('document_type', ['nid', 'passport', 'birth_certificate'])->default('nid');
            $table->string('full_name', 150);
            $table->string('document_number', 100);
            $table->date('date_of_birth')->nullable();
            
            // Uploaded document images
            $table->string('front_image');
            $table->string('back_image')->nullable(); // Required for NID, optional for passport/birth_certificate
            $table->string('selfie_image'); // Selfie with document / live face scan
            
            // AI Face Liveness & Inspection metadata (e.g. angle: left, right, eye-level, lighting)
            $table->json('liveness_data')->nullable();
            $table->json('ai_detection_meta')->nullable();
            
            // Verification Status: 'pending', 'approved', 'rejected'
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('user_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_note')->nullable();
            
            // Admin audit
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('document_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
