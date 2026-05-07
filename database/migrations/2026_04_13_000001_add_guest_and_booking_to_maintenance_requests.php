<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->foreignId('guest_id')->nullable()->after('room_id')->constrained('guests')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->after('guest_id')->constrained('bookings')->nullOnDelete();
            $table->string('category')->nullable()->after('title'); // e.g. plumbing, electrical, amenities, housekeeping, other
        });

        // Allow guest-submitted tickets (created_by can be null)
        DB::statement('ALTER TABLE maintenance_requests ALTER COLUMN created_by DROP NOT NULL');
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
            $table->dropForeign(['booking_id']);
            $table->dropColumn(['guest_id', 'booking_id', 'category']);
        });
    }
};
