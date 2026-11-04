<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 20);
            $table->string('interval', 20);
            $table->char('currency', 3);
            $table->unsignedBigInteger('amount_minor');
            $table->string('gateway_price_id')->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'gateway', 'interval', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_prices');
    }
};
