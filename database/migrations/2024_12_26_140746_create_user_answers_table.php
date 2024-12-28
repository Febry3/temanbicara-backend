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
        Schema::create('user_answers', function (Blueprint $table) {
            $table->id('user_answer_id');
            $table->integer('user_point');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('quiz_id')->unsigned();
            $table->bigInteger('answer_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('quiz_id')->references('quiz_id')->on('quizzes');
            $table->foreign('answer_id')->references('answer_id')->on('answers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_answers');
    }
};
