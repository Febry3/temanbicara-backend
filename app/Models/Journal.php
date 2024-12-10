<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $primaryKey = "journal_id";

    protected $fillable = [
        'title',
        'body',
        'stress_level',
        'mood_level',
        'user_id'
    ];
}
