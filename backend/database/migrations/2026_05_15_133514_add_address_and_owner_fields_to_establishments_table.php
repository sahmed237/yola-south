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
        Schema::table('establishments', function (Blueprint $table) {
            // Address Fields
            $table->string('street_address')->nullable()->after('inside_metropolis');
            $table->string('house_number')->nullable()->after('street_address');
            $table->string('city')->nullable()->after('house_number');
            $table->string('postal_code')->nullable()->after('city');

            // Owner Details (Optional)
            $table->string('owner_name')->nullable()->after('postal_code');
            $table->string('owner_phone')->nullable()->after('owner_name');
            $table->string('owner_email')->nullable()->after('owner_phone');
            $table->enum('owner_gender', ['male', 'female', 'corporate'])->nullable()->after('owner_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn([
                'street_address', 
                'house_number', 
                'city', 
                'postal_code', 
                'owner_name', 
                'owner_phone', 
                'owner_email', 
                'owner_gender'
            ]);
        });
    }
};
