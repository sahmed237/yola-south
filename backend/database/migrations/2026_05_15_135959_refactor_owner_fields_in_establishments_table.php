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
            $table->unsignedBigInteger('owner_id')->nullable()->after('occupant_id');
            $table->foreign('owner_id')->references('id')->on('establishment_owners')->onDelete('set null');

            $table->dropColumn([
                'owner_name',
                'owner_phone',
                'owner_email',
                'owner_gender'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn('owner_id');

            $table->string('owner_name')->nullable();
            $table->string('owner_phone')->nullable();
            $table->string('owner_email')->nullable();
            $table->enum('owner_gender', ['male', 'female', 'corporate'])->nullable();
        });
    }
};
