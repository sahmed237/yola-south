<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });

        Schema::create('revenue_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies');
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->string('frequency'); // daily, monthly, yearly
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained('establishments');
            $table->decimal('amount', 15, 2);
            $table->string('status'); // pending, success, failed
            $table->string('reference')->unique();
            $table->string('gateway')->default('paystack');
            $table->timestamps();
        });

        Schema::create('payment_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments');
            $table->foreignId('agency_id')->constrained('agencies');
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_splits');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('revenue_rules');
        Schema::dropIfExists('agencies');
    }
};
