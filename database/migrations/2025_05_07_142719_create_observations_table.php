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
        Schema::create('observations', function (Blueprint $table) {
            $table->id('observation_id');
            $table->longText("mood");
            $table->longText("sleep");
            $table->longText("stress");
            $table->longText("screen_time");
            $table->longText("activity");
            $table->bigInteger('response_id')->unsigned();
            $table->foreign('response_id')->references('response_id')->on('track_journal_response')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};
