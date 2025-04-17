<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    protected $primaryKey = "tracking_id";
    protected $fillable = [
        'bed_time',
        'mood_level',
        'stress_level',
        'screen_time',
        'activity',
        'user_id'
    ];
}
