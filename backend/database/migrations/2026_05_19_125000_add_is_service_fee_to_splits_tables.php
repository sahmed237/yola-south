<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_splits', function (Blueprint $table) {
            $table->boolean('is_service_fee')->default(false)->after('subaccount_code');
        });

        Schema::table('payment_splits', function (Blueprint $table) {
            $table->boolean('is_service_fee')->default(false)->after('net_amount');
        });
    }

    public function down(): void
    {
        Schema::table('payment_splits', function (Blueprint $table) {
            $table->dropColumn('is_service_fee');
        });

        Schema::table('invoice_splits', function (Blueprint $table) {
            $table->dropColumn('is_service_fee');
        });
    }
};
