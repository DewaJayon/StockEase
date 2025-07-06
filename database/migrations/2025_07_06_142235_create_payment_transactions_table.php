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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id');

            $table->string('gateway');
            $table->string('external_id')->nullable();
            $table->string('status');
            $table->decimal('amount', 10, 2);
            $table->string('payment_type');
            $table->string('qr_code_url')->nullable();
            $table->json('raw_response')->nullable();
            $table->dateTime('expired_at');

            $table->timestamps();
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
