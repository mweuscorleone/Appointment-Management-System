<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    protected $fillable = [
        'patient_id','sponsor_id', 'doctor_id', 'clinic_id', 'item_id', 'user_id'
    ];

    public function patient(){
        return $this->belongsTo(Patient::class, 'patient_id');
    }
    public function doctor(){
        return $this->belongsTo(User::class, 'doctor_id');
    }
    public function clinic(){
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
    public function item(){
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function sponsor(){
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }
    public function payment(){
        return $this->hasMany(Payment::class, 'check_in_id');
    }

}
