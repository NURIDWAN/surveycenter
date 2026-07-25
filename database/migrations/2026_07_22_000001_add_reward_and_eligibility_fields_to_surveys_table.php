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
        Schema::table('surveys', function (Blueprint $table) {
            $table->unsignedInteger('reward_amount')->default(0)->after('completed_at');
            $table->timestamp('deadline')->nullable()->after('reward_amount');
            $table->unsignedInteger('estimated_time_minutes')->nullable()->after('deadline');
            $table->json('eligibility_criteria')->nullable()->after('estimated_time_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn([
                'reward_amount',
                'deadline',
                'estimated_time_minutes',
                'eligibility_criteria',
            ]);
        });
    }
};
