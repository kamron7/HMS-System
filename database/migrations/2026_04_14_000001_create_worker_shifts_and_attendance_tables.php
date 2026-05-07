<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('shift_type')->default('regular'); // regular, overtime
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->text('start_note')->nullable();
            $table->text('end_note')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('started_at');
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('worker_shift_id')->nullable()->constrained('worker_shifts')->nullOnDelete();
            $table->enum('type', ['check_in', 'check_out', 'break_start', 'break_end']);
            $table->timestamp('logged_at');
            $table->string('ip_address')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('worker_shifts');
    }
};
