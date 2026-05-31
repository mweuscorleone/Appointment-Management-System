<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['name', 'consultation_type_id', 'item_category_id','status'];

    public function itemPrice(){
        return $this->hasMany(itemPrice::class, 'item_id');
    }
    public function payment(){
        return $this->hasMany(Payment::class, 'item_id');
    }
    public function itemCategory(){
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }
    public function consultationType(){
        return $this->belongsTo(ConsultationType::class, 'consultation_type_id');
    }
}
