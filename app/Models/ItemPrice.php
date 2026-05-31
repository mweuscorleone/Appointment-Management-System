<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPrice extends Model
{
    protected $fillable = [
        'item_id', 'sponsor_id', 'price'
    ];

    public function item(){
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function sponsor(){
        return $this->belongsTo(Item::class, 'sponsor_id');
        
    }
}
