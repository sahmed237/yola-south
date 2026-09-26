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
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('code');
            $table->string('bank_code')->nullable()->after('bank_name');
            $table->string('account_number')->nullable()->after('bank_code');
            $table->string('account_name')->nullable()->after('account_number');
            $table->boolean('status')->default(true)->after('account_name');
        });

        Schema::table('revenue_rules', function (Blueprint $table) {
            $table->text('sql_rule')->nullable()->after('amount');
        });

        Schema::create('paystack_sub_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->string('subaccount_code')->unique();
            $table->boolean('active')->default(true);
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('monnify_sub_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->string('subaccount_code')->unique(); // subAccountCode
            $table->boolean('active')->default(true);
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monnify_sub_accounts');
        Schema::dropIfExists('paystack_sub_accounts');

        Schema::table('revenue_rules', function (Blueprint $table) {
            $table->dropColumn('sql_rule');
        });

        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_code', 'account_number', 'account_name', 'status']);
        });
    }
};
