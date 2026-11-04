<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway', 20);
            $table->string('gateway_payment_id')->nullable();
            $table->bigInteger('amount')->unsigned();
            $table->string('currency', 3);
            $table->string('status', 20)->default('succeeded'); // succeeded|refunded|failed
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['workspace_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
