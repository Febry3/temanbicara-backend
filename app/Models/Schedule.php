<?php

namespace App\Models;

use Carbon\Carbon;
use Date;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'schedules';
    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'available_date',
        'start_time',
        'end_time',
        'status',
        'is_available',
        'counselor_id',
    ];

    protected function casts(): array
    {
        return [
            'available_date' => 'date',
            'is_available' => 'boolean',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
    public function consultation()
    {
        return $this->hasOne(Consultations::class, 'schedule_id');
    }
}
