<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationType extends Model
{
    protected $fillable = ['name', 'status'];

    public function item(){
        return $this->hasMany(Item::class, 'consultation_type_id');
    }
}
