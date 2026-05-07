<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_ref', 10)->nullable()->unique()->after('id');
        });

        // Backfill existing bookings using raw DB to avoid soft-delete dependency
        \DB::table('bookings')->whereNull('booking_ref')->orderBy('id')->each(function ($booking) {
            \DB::table('bookings')->where('id', $booking->id)->update(['booking_ref' => self::generate()]);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('booking_ref');
        });
    }

    private static function generate(): string
    {
        do {
            $ref = 'H-' . strtoupper(Str::random(6));
        } while (\DB::table('bookings')->where('booking_ref', $ref)->exists());

        return $ref;
    }
};
