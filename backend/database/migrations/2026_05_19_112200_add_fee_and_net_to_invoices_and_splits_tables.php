<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('payment_fee', 15, 2)->default(0.00)->after('total_amount');
        });

        Schema::table('invoice_splits', function (Blueprint $table) {
            $table->decimal('net_amount', 15, 2)->default(0.00)->after('amount');
        });

        Schema::table('payment_splits', function (Blueprint $table) {
            $table->decimal('net_amount', 15, 2)->default(0.00)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('payment_splits', function (Blueprint $table) {
            $table->dropColumn('net_amount');
        });

        Schema::table('invoice_splits', function (Blueprint $table) {
            $table->dropColumn('net_amount');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('payment_fee');
        });
    }
};
