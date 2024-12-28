<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultations extends Model
{

    protected $primaryKey = 'consultations_id';

    protected $fillable = [
        'status',
        'description',
        'problem',
        'summary',
        'schedule_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function schedule() {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'id');
    }
}
