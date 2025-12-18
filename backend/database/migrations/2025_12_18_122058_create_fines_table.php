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
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->boolean('paid')->default(false);
            $table->date('payment_date')->nullable();
            $table->enum('payment_method', ['cash', 'transfer', 'e-wallet', 'credit_card'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('transaction_id');
            $table->index('paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
