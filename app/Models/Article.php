<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    //
    protected $table = 'artikels';
    protected $primaryKey = 'artikel_id';
    protected $fillable = [
        'title',
        'content',
        'image',
        'status',
        'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


