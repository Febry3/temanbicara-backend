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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id('consultation_id');
            $table->enum('status', ['Incoming', 'Done', 'Cancelled'])->default('Incoming');
            $table->longText('description');
            $table->longText('problem');
            $table->longText('summary')->nullable()->default('Segera ...');
            $table->bigInteger('patient_id')->unsigned();
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('schedule_id')->unsigned();
            $table->foreign('schedule_id')->references('schedule_id')->on('schedules');
            $table->bigInteger('payment_id')->unsigned();
            $table->foreign('payment_id')->references('payment_id')->on('payments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
