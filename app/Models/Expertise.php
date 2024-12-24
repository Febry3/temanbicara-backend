<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expertise extends Model
{
    protected $primaryKey = 'expertise_id';
    protected $fillable = [
        'type',
        'user_id',
    ];
}
