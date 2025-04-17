<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $primaryKey = 'assessment_id';

    protected $fillable = [
        'topic',
        'goal',
        'sleep_quality',
        'have_consulted',
        'consumed_medicine',
        'mbti',
        'stress_level',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'have_consulted' => 'boolean',
        ];
    }
}
