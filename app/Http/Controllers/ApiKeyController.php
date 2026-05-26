<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ApiKey;

class ApiKeyController extends Controller
{
    public function store(){

        $plainKey = Str::random(200);
        $hashedKey = hash('sha256', $plainKey);

        ApiKey::create([
            'name' => 'another backend app',
            'api_key' => $hashedKey
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Api protection key created successfully!',
            'api key' => $plainKey
        ], 201);
    }
}
