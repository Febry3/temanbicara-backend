<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Observation extends Model
{
    protected $table = 'observations';
    protected $primaryKey = "observation_id";
    protected $fillable = [
        'mood',
        'sleep',
        'stress',
        'screen_time',
        'activity',
        'response_id'
    ];
    public function track_journal_response(): BelongsTo
    {
        return $this->belongsTo(TrackJournalResponse::class, 'response_id', 'response_id');
    }
}
