<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    protected $fillable = ['name', 'sponsor_type'];

    public function checkIn(){
        return $this->hasMany(CheckIn::class, 'sponsor_id');
    }
    public function itmePrice(){
        return $this->hasMany(ItemPrice::class, 'sponsor_id');
    }
    public function payment(){
        return $this->hasMany(Payment::class, 'sponsor_id');
    }
}
