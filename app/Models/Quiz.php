<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $primaryKey = 'quiz_id';

    protected $fillable = [
        'question',
        'user_id',
        'answer_id'
    ];
    public function answers()
    {
        return $this->hasMany(Answer::class, 'quiz_id', 'quiz_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function user_answers()
    {
        return $this->hasMany(user_answer::class, 'quiz_id', 'quiz_id');
    }
}
