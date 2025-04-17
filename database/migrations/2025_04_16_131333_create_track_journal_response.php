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
        Schema::create('track_journal_response', function (Blueprint $table) {
            $table->id("response_id");
            $table->integer("metrix");
            $table->longText("assesment");
            $table->longText("short_term");
            $table->longText("long_term");
            $table->longText("closing");
            $table->bigInteger('tracking_id')->unsigned();
            $table->foreign('tracking_id')->references('tracking_id')->on('trackings')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('track_journal_response');
    }
};
