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
        Schema::create('price_histories', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('product_id')->constrained()->cascadeOnDelete();
            $blueprint->foreignId('user_id')->constrained()->cascadeOnDelete();
            $blueprint->decimal('old_purchase_price', 19, 4);
            $blueprint->decimal('new_purchase_price', 19, 4);
            $blueprint->decimal('old_selling_price', 19, 4);
            $blueprint->decimal('new_selling_price', 19, 4);
            $blueprint->string('reason')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
