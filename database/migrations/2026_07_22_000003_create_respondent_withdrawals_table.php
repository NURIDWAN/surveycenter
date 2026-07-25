<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respondent_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('provider_name', 100);
            $table->string('account_number', 50);
            $table->string('account_holder_name', 255);
            $table->string('status', 20)->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status', 'respondent_withdrawals_status_index');
            $table->index('user_id', 'respondent_withdrawals_user_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respondent_withdrawals');
    }
};
