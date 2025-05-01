<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class user_answer extends Model
{
    protected $primaryKey = 'user_answer_id';

    protected $fillable = [
        'user_point',
        'user_id',
        'quiz_id',
        'answer_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function quizzes()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }
    public function answers()
    {
        return $this->belongsTo(Answer::class, 'quiz_id', 'quiz_id');
    }
}
