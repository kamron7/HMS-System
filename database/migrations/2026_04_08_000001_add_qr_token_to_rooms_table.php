<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('qr_token', 32)->nullable()->unique()->after('notes');
        });

        // Backfill existing rooms
        \App\Models\Room::whereNull('qr_token')->each(function ($room) {
            $room->update(['qr_token' => Str::random(32)]);
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
