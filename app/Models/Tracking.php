<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    protected $primaryKey = "tracking_id";
    protected $fillable = [
        'sleep_quality',
        'mood_level',
        'stress_level',
        'user_id'
    ];
}
