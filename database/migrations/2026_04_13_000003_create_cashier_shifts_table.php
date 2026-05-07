<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('previous_shift_id')->nullable()->constrained('cashier_shifts')->nullOnDelete();
            $table->decimal('opening_expected', 14, 2)->comment('Expected from previous shift closing');
            $table->decimal('opening_actual', 14, 2)->comment('Actual counted by cashier');
            $table->decimal('opening_difference', 14, 2)->comment('actual - expected');
            $table->string('shift')->nullable()->comment('morning/evening/night');
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->decimal('cash_in', 14, 2)->default(0)->comment('Total cash received during shift');
            $table->decimal('cash_out', 14, 2)->default(0)->comment('Total cash refunds during shift');
            $table->decimal('closing_expected', 14, 2)->nullable()->comment('opening_actual + cash_in - cash_out');
            $table->decimal('closing_actual', 14, 2)->nullable()->comment('Actual counted at end');
            $table->decimal('closing_difference', 14, 2)->nullable()->comment('closing_actual - closing_expected');
            $table->text('notes_open')->nullable();
            $table->text('notes_close')->nullable();
            $table->string('status')->default('open'); // open, closed
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_shifts');
    }
};
