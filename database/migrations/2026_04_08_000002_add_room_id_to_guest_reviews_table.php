<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_reviews', function (Blueprint $table) {
            // Make booking_id and guest_id nullable (reviews can come from room QR with no known guest)
            $table->foreignId('booking_id')->nullable()->change();
            $table->foreignId('guest_id')->nullable()->change();

            // Link to room
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('guest_reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
            $table->foreignId('booking_id')->nullable(false)->change();
            $table->foreignId('guest_id')->nullable(false)->change();
        });
    }
};
