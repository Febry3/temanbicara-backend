<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Journal extends Model
{
    protected $primaryKey = "journal_id";

    protected $fillable = [
        'title',
        'body',
        'image_url',
        'tracking_id',
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function tracking(): BelongsTo
    {
        return $this->belongsTo(Tracking::class, 'tracking_id', 'tracking_id');
    }
}
