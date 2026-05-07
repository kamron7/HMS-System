<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('applied_promo_code', 50)->nullable()->after('notes');
            $table->decimal('discount_amount', 12, 2)->nullable()->after('applied_promo_code');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['applied_promo_code', 'discount_amount']);
        });
    }
};
