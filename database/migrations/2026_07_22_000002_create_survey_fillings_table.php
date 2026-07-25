<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_fillings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('sedang_dikerjakan');
            $table->string('proof_file_path', 500)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('rejection_reason_id')->nullable()->constrained('rejection_reasons')->nullOnDelete();
            $table->text('rejection_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->unique(['survey_id', 'user_id'], 'survey_fillings_survey_user_unique');
            $table->index('status', 'survey_fillings_status_index');
            $table->index(['user_id', 'status'], 'survey_fillings_user_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_fillings');
    }
};
