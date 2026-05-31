<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = ['full_name', 'sponsor_id','date_of_birth', 'gender', 'phone', 'address'];

    public function appointment(){
        return $this->hasMany(Appointment::class, 'patient_id');
    }
    public function check_in(){
        return $this->hasMany(ChechIn::class, 'patient_id');
    }
    public function payment(){
        return $this->hasMany(Payment::class, 'patient_id');
    }

}
