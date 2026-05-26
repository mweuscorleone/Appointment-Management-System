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
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', [
                'scheduled',
                'completed',
                'not attended',
                'postponed',
                'cancelled'
            ])->change()->nullable()->default('scheduled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', [
                'scheduled',
                'canceled',
                'served',
                'postponed'
            ])->change()->default('scheduled');
        });
    }
};
