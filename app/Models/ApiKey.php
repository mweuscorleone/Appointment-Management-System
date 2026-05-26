<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = ['name','api_key','is_acitve'];


    protected $casts = [
                        'is_active' => 'boolean'
    ];
}
