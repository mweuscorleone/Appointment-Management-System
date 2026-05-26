<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request){
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:4'
        ],
        [
            'email.exists' => 'Provided Email Do not exist please register'
        ]);

        if(!$token = Auth::guard('api')->attempt($request->only('email', 'password'))){
            return response()->json(['message' => 'invalid credential please try again'], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'login successfully!',
            'token' => $token,
            'token will expire in' => Carbon::now()->addMinutes(auth('api')->factory()->getTTL())->diffForHumans()
        ]);
    }
    public function logout(Request $request){
        auth('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'logout successfully!'
        ], 200);
    }
    
    public function passwordResetToken(Request $request){
        $request->validate([
            'email' => 'required|email|exists:users,email'

        ],
        [
            'email.exists' => 'provided email do not exists please try again with valid email'
        ]);
        
        $plainToken = Str::random(100);

        $hashedToken = hash('sha256', $plainToken);

        DB::table('table_password_reset_tokens')->updateOrInsert([
            'email' => $request->email
        ],
        [
            'token' => $hashedToken,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $userToken = DB::table('table_password_reset_tokens')->where('email', $request->email)->first();
        $expired_at = Carbon::parse($userToken->created_at)->addMinutes(3);

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset token generated successfully',
            'token' => $plainToken,
            'token expire at' => $expired_at->format('Y-m-d-H:m:s'),
            'token expire in' => $expired_at->diffForHumans()
        ], 200);

    }


    public function resetPassword(Request $request){
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:4'

        ],
        [
            'email.exists' => 'email do not exists please try again'
        ]);
        $user = DB::table('users')->where('email', $request->email)->first();
        $userToken = DB::table('table_password_reset_tokens')->where('email', $user->email)->first();
        if(!$userToken){
            return response()->json([
                'message' => 'Token already used, please generate another token'
            ], 400);
        }

        $plainToken = $request->token;
        $hashedToken = hash('sha256', $plainToken);

        if(!hash_equals($userToken->token, $hashedToken)){
            return response([
                'status' => 'failed',
                'message' => 'invalid token please try again!'
            ], 400);
        }

        $exired = Carbon::parse($userToken->created_at)->addMinutes(3)->isPast();

        if($exired){
            return response()->json([
                'status' => 'failed',
                'message' => 'token expired please request token again'
            ], 400);
        }

        DB::table('users')->where('email', $user->email)->update([
            'password' => Hash::make($request->password),
            'updated_at' => now()
        ]);

        DB::table('table_password_reset_tokens')->where('email', $user->email)->delete();


        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully!'
        ], 200);
    }
}

