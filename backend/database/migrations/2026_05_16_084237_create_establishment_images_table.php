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
        Schema::create('establishment_images', function (Blueprint $label) {
            $label->id();
            $label->foreignId('establishment_id')->constrained('establishments')->onDelete('cascade');
            $label->string('image_path');
            $label->boolean('is_primary')->default(false);
            $label->decimal('lat', 11, 8)->nullable();
            $label->decimal('lng', 11, 8)->nullable();
            $label->string('device_info')->nullable();
            $label->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('establishment_images');
    }
};
