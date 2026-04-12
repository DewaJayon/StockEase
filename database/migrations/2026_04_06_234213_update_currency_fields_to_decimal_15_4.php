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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('purchase_price', 19, 4)->change();
            $table->decimal('selling_price', 19, 4)->change();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('total', 19, 4)->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('price', 19, 4)->change();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('total', 19, 4)->change();
            $table->decimal('paid', 19, 4)->change();
            $table->decimal('change', 19, 4)->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('price', 19, 4)->change();
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->decimal('amount', 19, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('purchase_price', 10, 2)->change();
            $table->decimal('selling_price', 10, 2)->change();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedInteger('total')->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->change();
            $table->decimal('paid', 10, 2)->change();
            $table->decimal('change', 10, 2)->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });
    }
};
