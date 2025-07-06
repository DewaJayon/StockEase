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
        Schema::create('purcase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purcase_id');
            $table->foreignId('product_id');

            $table->unsignedInteger('qty');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->foreign('purcase_id')->references('id')->on('purcases')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purcase_items');
    }
};
