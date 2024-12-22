<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedules extends Model
{
    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'available_date',
        'start_time',
        'end_time',
        'is_available',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'available_date' => 'date',
            'is_available' => 'boolean',
        ];
    }
}
