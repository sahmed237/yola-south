<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop foreign keys referencing revenue_rules
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['revenue_rule_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['revenue_rule_id']);
        });

        // 2. Rename table
        Schema::rename('revenue_rules', 'revenue_heads');

        // 3. Rename column and recreate foreign key in invoice_items
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->renameColumn('revenue_rule_id', 'revenue_head_id');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreign('revenue_head_id')
                ->references('id')
                ->on('revenue_heads')
                ->cascadeOnDelete();
        });

        // 4. Rename column and recreate foreign key in payments
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('revenue_rule_id', 'revenue_head_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('revenue_head_id')
                ->references('id')
                ->on('revenue_heads')
                ->nullOnDelete();
        });

        // 5. Update permission if present
        DB::table('permissions')->where('name', 'manage revenue rules')->update([
            'name' => 'manage revenue heads',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['revenue_head_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['revenue_head_id']);
        });

        Schema::rename('revenue_heads', 'revenue_rules');

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->renameColumn('revenue_head_id', 'revenue_rule_id');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreign('revenue_rule_id')
                ->references('id')
                ->on('revenue_rules')
                ->cascadeOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('revenue_head_id', 'revenue_rule_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('revenue_rule_id')
                ->references('id')
                ->on('revenue_rules')
                ->nullOnDelete();
        });

        DB::table('permissions')->where('name', 'manage revenue heads')->update([
            'name' => 'manage revenue rules',
        ]);
    }
};
