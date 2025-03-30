<?php

namespace App\Models;

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
    ];

    //cuma bisa bank transfer
}
