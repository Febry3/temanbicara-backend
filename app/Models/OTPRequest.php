<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OTPRequest extends Model
{
    protected $primaryKey = "otp_request_id";

    protected $table = 'otp_requests';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'otp',
        'expired_at'
    ];
}
