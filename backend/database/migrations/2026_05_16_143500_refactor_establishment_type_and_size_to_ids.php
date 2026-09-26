<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add new columns
        Schema::table('establishments', function (Blueprint $table) {
            $table->foreignId('establishment_type_id')->nullable()->after('name')->constrained('establishment_types');
            $table->foreignId('establishment_size_id')->nullable()->after('establishment_type_id')->constrained('establishment_sizes');
        });

        // 2. Data migration
        $establishments = DB::table('establishments')->get();
        foreach ($establishments as $establishment) {
            $typeId = DB::table('establishment_types')->where('value', $establishment->type)->value('id');
            $sizeId = DB::table('establishment_sizes')->where('value', $establishment->size)->value('id');

            DB::table('establishments')->where('id', $establishment->id)->update([
                'establishment_type_id' => $typeId,
                'establishment_size_id' => $sizeId,
            ]);
        }

        // 3. Drop old columns
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn(['type', 'size']);
        });
    }

    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name');
            $table->string('size')->nullable()->after('type');
        });

        // Reverse data migration
        $establishments = DB::table('establishments')->get();
        foreach ($establishments as $establishment) {
            $typeValue = DB::table('establishment_types')->where('id', $establishment->establishment_type_id)->value('value');
            $sizeValue = DB::table('establishment_sizes')->where('id', $establishment->establishment_size_id)->value('value');

            DB::table('establishments')->where('id', $establishment->id)->update([
                'type' => $typeValue,
                'size' => $sizeValue,
            ]);
        }

        Schema::table('establishments', function (Blueprint $table) {
            $table->dropForeign(['establishment_type_id']);
            $table->dropForeign(['establishment_size_id']);
            $table->dropColumn(['establishment_type_id', 'establishment_size_id']);
        });
    }
};
