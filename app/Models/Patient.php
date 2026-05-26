<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = ['full_name', 'date_of_birth', 'gender', 'phone', 'address'];

    public function appointment(){
        return $this->hasMany(Appointment::class, 'patient_id');
    }
}
