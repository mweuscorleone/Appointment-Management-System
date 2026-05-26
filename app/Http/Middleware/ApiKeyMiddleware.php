<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKey;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {  
        $plainKey = $request->header('X-API-KEY');

        if(!$plainKey){
            return response()->json([
                'message' => 'please provide an API Key'
            ], 401);

        }

        $hashedKey = hash('sha256', $plainKey);
       $apiKey =  ApiKey::where('api_key', $hashedKey)->where('is_active', true)->first();

       if(!$apiKey){
        return response()->json([
            'message' => 'API KEY is Invalid or not Active, please try again!'
        ], 401);
       }

       

        return $next($request);
    }
}
