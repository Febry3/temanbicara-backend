<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultations extends Model
{

    protected $primaryKey = 'consultations_id';

    protected $fillable = [
        'is_accepted',
        'contact_url',
        'meet_url',
        'note',
        'description',
        'schedule_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
        ];
    }
}
