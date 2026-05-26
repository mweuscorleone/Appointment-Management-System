<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $fillable = ['name', 'location'];


    public function appointment(){
        return $this->hasMany(Appoitment::class, 'clinic_id');
    }
}
