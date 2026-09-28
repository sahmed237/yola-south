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
        Schema::table('shop_allocations', function (Blueprint $table) {
            $table->text('action_required_notes')->nullable()->after('rejection_reason');
            $table->timestamp('action_requested_at')->nullable()->after('action_required_notes');
            $table->timestamp('action_responded_at')->nullable()->after('action_requested_at');
        });

        // Modify status column to varchar to allow action_required and smooth stage transitions
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `shop_allocations` MODIFY `status` VARCHAR(40) NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_allocations', function (Blueprint $table) {
            $table->dropColumn(['action_required_notes', 'action_requested_at', 'action_responded_at']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `shop_allocations` MODIFY `status` ENUM('pending','review','recommended','approved','allocated','payment','completed','rejected','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
