<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['doctor_id', 'patient_id', 'clinic_id', 'appointment_date', 'status'];

    public function doctor(){
        return $this->belongsTo(User::class, 'doctor_id');
    }
    public function patient(){
        return $this->belongsTo(Patient::class, 'patient_id');
    }
    public function clinic(){
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
}
