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
        Schema::create('establishment_update_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained('establishments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // requested_by
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('approval_remarks')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establishment_update_requests');
    }
};
