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
        // 1. Markets Table
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('ward_id')->nullable()->constrained('wards')->nullOnDelete();
            $table->string('ward_name')->nullable();
            $table->string('address')->nullable();
            $table->unsignedInteger('blocks_count')->default(1);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'under_renovation'])->default('active');
            $table->decimal('revenue_ytd', 15, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Shops Table
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained('markets')->cascadeOnDelete();
            $table->string('shop_code')->unique();
            $table->string('block_name')->default('Block A');
            $table->string('shop_number');
            $table->string('size')->default('3.0 × 4.0 m');
            $table->string('type')->default('Lock-up shop');
            $table->decimal('monthly_rent', 12, 2)->default(0.00);
            $table->decimal('annual_rent', 12, 2)->default(0.00);
            $table->enum('status', ['vacant', 'occupied', 'arrears', 'reserved', 'maintenance'])->default('vacant');
            $table->string('current_occupant_name')->nullable();
            $table->string('current_occupant_phone')->nullable();
            $table->string('current_occupant_nin')->nullable();
            $table->unsignedBigInteger('current_allocation_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Shop Allocations Table
        Schema::create('shop_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('application_no')->unique(); // e.g. ALL-YSLG-2026-000186
            $table->foreignId('market_id')->constrained('markets')->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained('shops')->nullOnDelete();
            $table->string('applicant_name');
            $table->string('applicant_phone');
            $table->string('applicant_email');
            $table->string('applicant_nin_bvn')->nullable();
            $table->text('applicant_address')->nullable();
            $table->string('trade_type')->default('Retail');
            $table->string('requested_size')->nullable();
            $table->string('passport_photo')->nullable();
            $table->string('id_document')->nullable();
            $table->string('business_reg_doc')->nullable();
            
            // 7-Stage Workflow
            $table->unsignedTinyInteger('stage')->default(1);
            $table->enum('status', [
                'pending',      // Stage 1
                'review',       // Stage 2
                'recommended',  // Stage 3
                'approved',     // Stage 4
                'allocated',    // Stage 5
                'payment',      // Stage 6
                'completed',    // Stage 7 (Card issued)
                'rejected',
                'cancelled'
            ])->default('pending');

            $table->decimal('rent_amount', 12, 2)->default(0.00);
            $table->decimal('allocation_fee', 12, 2)->default(5000.00);
            $table->enum('payment_status', ['unpaid', 'paid', 'exempted'])->default('unpaid');
            $table->string('payment_reference')->nullable();
            $table->string('invoice_no')->nullable();
            
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('allocated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('officer_recommendation')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('conditions')->nullable();
            $table->string('tracking_hash')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Shop Application OTPs
        Schema::create('shop_application_otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('otp', 6);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_application_otps');
        Schema::dropIfExists('shop_allocations');
        Schema::dropIfExists('shops');
        Schema::dropIfExists('markets');
    }
};
