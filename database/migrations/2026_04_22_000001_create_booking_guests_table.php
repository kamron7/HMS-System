<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['booking_id', 'guest_id']);
        });

        // Back-fill existing bookings
        \DB::table('bookings')
            ->whereNotNull('guest_id')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->each(function ($b) {
                \DB::table('booking_guests')->insertOrIgnore([
                    'booking_id' => $b->id,
                    'guest_id'   => $b->guest_id,
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_guests');
    }
};
