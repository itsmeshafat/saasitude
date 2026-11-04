<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 20);
            $table->string('gateway_customer_id');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'gateway']);
            $table->index('gateway_customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_customers');
    }
};
