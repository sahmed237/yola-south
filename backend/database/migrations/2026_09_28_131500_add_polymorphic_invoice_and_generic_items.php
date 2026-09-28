<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Invoices: make establishment_id nullable and add polymorphic morphs
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['establishment_id']);
            });

            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('establishment_id')->nullable()->change()->constrained('establishments')->nullOnDelete();
            });
        } else {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('establishment_id')->nullable()->change();
            });
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->nullableMorphs('invoiceable');
        });

        // 2. Payments: make establishment_id nullable and add polymorphic morphs
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['establishment_id']);
            });

            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('establishment_id')->nullable()->change()->constrained('establishments')->nullOnDelete();
            });
        } else {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('establishment_id')->nullable()->change();
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->nullableMorphs('payable');
        });

        // 3. Generic Invoice Items Table
        Schema::create('generic_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 4. Backfill existing records with polymorphic mapping to Establishment
        try {
            DB::statement("UPDATE invoices SET invoiceable_type = 'App\\\\Models\\\\Establishment', invoiceable_id = establishment_id WHERE establishment_id IS NOT NULL AND invoiceable_id IS NULL");
            DB::statement("UPDATE payments SET payable_type = 'App\\\\Models\\\\Establishment', payable_id = establishment_id WHERE establishment_id IS NOT NULL AND payable_id IS NULL");
        } catch (\Throwable $e) {
            // Ignored on fresh databases/tests
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generic_invoice_items');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropMorphs('payable');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropMorphs('invoiceable');
        });
    }
};
