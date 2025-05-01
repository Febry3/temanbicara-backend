<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackJournalResponse extends Model
{
    protected $table = 'track_journal_response';
    protected $primaryKey = "response_id";
    protected $fillable = [
        'metrix',
        'assesment',
        'short_term',
        'long_term',
        'closing',
        'tracking_id'
    ];
    public function tracking(): BelongsTo
    {
        return $this->belongsTo(Tracking::class, 'tracking_id', 'tracking_id');
    }
}
