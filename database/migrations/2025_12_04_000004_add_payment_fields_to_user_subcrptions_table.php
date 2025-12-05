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
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->string('payment_reference')->nullable()->after('payment');
            $table->string('payment_gateway')->nullable()->after('payment_reference');
            $table->string('currency', 3)->nullable()->after('price');
            $table->string('card_last_four', 4)->nullable()->after('payment_gateway');
            $table->string('card_brand')->nullable()->after('card_last_four');
            $table->json('payment_meta')->nullable()->after('card_brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_reference',
                'payment_gateway',
                'currency',
                'card_last_four',
                'card_brand',
                'payment_meta',
            ]);
        });
    }
};

