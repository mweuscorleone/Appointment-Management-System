<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'patient_id', 'check_in_id', 'sponsor_id', 'item_id', 'clinic_id', 'user_id', 'doctor_id', 'dicount',
        'amount', 'payment_method', 'transaction_id', 'transaction_type', 'payment_status'
    ];

    public function patient(){
        return $this->belongsTo(Patient::class, 'patient_id');

    }
    public function checkIn(){
        return $this->belongsTo(CheckIn::class, 'check_in_id');
    }
    public function sponsor(){
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }
    public function item(){
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function clinic(){
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
    public function doctor(){
        return $this->belongsTo(User::class, 'doctor_id');
    }
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

}
