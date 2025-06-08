<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultations extends Model
{

    protected $primaryKey = 'consultation_id';

    protected $fillable = [
        'status',
        'description',
        'problem',
        'summary',
        'schedule_id',
        'patient_id',
        'payment_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }
    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'schedule_id');
    }
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    public function endConsultation()
    {
        $this->status = "Done";
        $this->save();
        Schedule::where("schedule_id", $this->schedule_id)->update(["status" => "Done"]);
    }
}
