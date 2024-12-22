<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class artikel extends Model
{
    //
    protected $primaryKey = 'artikel_id';
    protected $fillable = [
        'title',
        'content',
        'image',
        'user_id'
    ];
}
