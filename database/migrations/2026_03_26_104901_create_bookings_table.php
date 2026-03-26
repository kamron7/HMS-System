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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->foreignId('guest_id')->constrained('guests')->restrictOnDelete();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->string('status')->default('pending');
            $table->decimal('total_price', 12, 2);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['room_id', 'check_in_date', 'check_out_date']);
            $table->index('guest_id');
            $table->index('status');
            $table->index('check_in_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
