<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultations extends Model
{

    protected $primaryKey = 'consultation_id';

    protected $fillable = [
        'status',
        'description',
        'problem',
        'summary',
        'schedule_id',
        'patient_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }

    public function schedule() {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'schedule_id');
    }
}
