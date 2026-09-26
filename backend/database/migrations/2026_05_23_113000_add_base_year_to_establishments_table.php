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
        Schema::table('establishments', function (Blueprint $table) {
            $table->integer('base_year')->nullable()->after('status');
        });

        // Set default base_year for existing establishments
        $defaultBaseYear = (int) env('REVENUE_BASE_YEAR', date('Y'));
        DB::table('establishments')->update(['base_year' => $defaultBaseYear]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn('base_year');
        });
    }
};
