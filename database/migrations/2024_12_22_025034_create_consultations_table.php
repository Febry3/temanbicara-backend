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
            $table->boolean('is_accepted');
            $table->string('contact_url');
            $table->string('meet_url');
            $table->string('note');
            $table->string('description');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('schedule_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('schedule_id')->references('schedule_id')->on('schedules');
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
