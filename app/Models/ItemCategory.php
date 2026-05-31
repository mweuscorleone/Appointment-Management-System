<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $fillable = ['name', 'status'];


    public function item(){
        return $this->hasMany(Item::class, 'item_category_id');
    }
}
