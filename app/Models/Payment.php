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

    public function completePayment()
    {
        $consultation = Consultations::where('transaction_id', $this->payment_id)->first();
        Schedule::where('schedule_id', $consultation->schedule_id)->update(['status' => 'Available']);
        $consultation->update(['status' => 'Done']);
        $this->payment_status = 'Success';
        $this->save();
        return $this;
    }
}
