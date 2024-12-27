<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $primaryKey = 'answer_id';

    protected $fillable = [
        'option',
        'point',
        'quiz_id'
    ];
    public function quizzes()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }
    public function user_answers()
    {
        return $this->hasMany(user_answer::class, 'user_answer_is', 'user_answer_is');
    }
}
