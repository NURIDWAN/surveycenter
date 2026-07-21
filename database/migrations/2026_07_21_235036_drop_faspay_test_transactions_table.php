<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('faspay_test_transactions');
    }

    public function down(): void
    {
        Schema::create('faspay_test_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending');
            $table->string('trx_id')->nullable();
            $table->string('payment_url')->nullable();
            $table->json('webhook_payload')->nullable();
            $table->timestamps();
        });
    }
};
