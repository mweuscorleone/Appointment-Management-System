<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:4',
            'role' => 'required|in:doctor,admin,reception'
        ],
        [
            'email.unique' => 'email address already exists please try again',
            'password.min' => 'password minimum length must be atleast 4 and more',
            'role.in' => 'role do not exist, user role must exist in (doctor, admin, reception'
        ]
        );

        $user  = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role?? 'reception',
            'email_verified_at'=>  now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'user created successfully!',
            'user' => $user
        ], 201);
    }
    public function update(Request $request, $id){
        $user = User::findOrFail($id);

        $fields = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email',
            'password' => 'sometimes|string|min:4',
            'role' => 'sometimes|in:doctor,admin,reception'
        ]);

        if(isset($fields['password'])){
            $fields['password'] = Hash::make($fields['password']);
        }

        $user->update($fields);

        return response()->json([
            'status' => 'success',
            'message' => 'user details updated successully!',
            'updated fields' => array_keys($fields)
        ], 200);
    }

    public function destroy($id){
        $user = User::findOrFail($id);

        $user->delete();

        return response()->json([
            'message' => 'user deleted successfully!'
        ], 200);
    }

    public function index(){
        $users = User::all();

        return response()->json([
            'status' => true,
            'users' => $users,
            'total users' => $users->count()
        ], 200);
    }
}
