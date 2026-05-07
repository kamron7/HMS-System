<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('passport_number', 50)->nullable()->after('phone');
            $table->date('birth_date')->nullable()->after('passport_number');
            $table->string('position', 100)->nullable()->after('birth_date');
            $table->date('hire_date')->nullable()->after('position');
            $table->string('avatar')->nullable()->after('hire_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'passport_number', 'birth_date', 'position', 'hire_date', 'avatar']);
        });
    }
};
