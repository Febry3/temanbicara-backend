<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $primaryKey = "payment_id";

    protected $fillable = [
        'amount',
        'payment_status',
        'expired_date',
        'bank',
        'va_number',
        'payment_method',
        'transaction_id'
    ];
    public function consultation()
{
    return $this->hasOne(Consultations::class, 'payment_id');
}
}
