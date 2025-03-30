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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->bigInteger('amount');
            $table->enum('payment_status', ['Pending', 'Failed', 'Success', 'Cancelled'])->default('Pending');
            $table->date('expired_date');
            $table->string('bank');
            $table->string('va_number');
            $table->string('payment_method')->default('Bank Transfer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
