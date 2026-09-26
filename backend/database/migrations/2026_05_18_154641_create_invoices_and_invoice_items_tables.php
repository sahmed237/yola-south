<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Invoices – one record per checkout session (pending until gateway confirms)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained('establishments')->onDelete('cascade');
            $table->string('reference')->unique();               // e.g. PUB-PAYSTACK-1716047200-4321
            $table->string('gateway');                           // Paystack | Monnify
            $table->string('email');                             // taxpayer email
            $table->decimal('total_amount', 15, 2);
            $table->string('status')->default('pending');        // pending | success | failed | cancelled
            $table->json('metadata')->nullable();                // extra gateway payload / notes
            $table->timestamps();
        });

        // Invoice Items – one row per revenue rule line being settled
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('revenue_rule_id')->constrained('revenue_rules')->onDelete('cascade');
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->string('period');                            // e.g. "2024 Annual", "2024 Q1"
            $table->decimal('amount', 15, 2);                   // outstanding amount being settled
            $table->timestamps();
        });

        // Invoice Splits – agency share of the total checkout amount
        Schema::create('invoice_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->decimal('amount', 15, 2);                   // absolute amount going to this agency
            $table->decimal('ratio', 8, 4);                     // percentage (0-100)
            $table->string('subaccount_code')->nullable();       // gateway subaccount code
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_splits');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
