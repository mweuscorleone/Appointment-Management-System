<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sponsor;

class SponsorController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|unique:sponsors,name',
            'sponsor_type' => 'required|string|in:cash,credit'
        ],
        [
            'name.unique' => 'provided sponsor name already existed please try again!',
            'sponsor_type.in' => 'provided sponsor type must be cash or credit' 
        ]);

        $sponsor = Sponsor::create([
            'name' => $request->name,
            'sponsor_type' => $request->sponsor_type
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'sponsor created successfully!',
            'sponsor' => $sponsor
        ], 201);

    }
    public function update(Request $request, $id){
        $sponsor = Sponsor::findOrFail($id);

        $fields = $request->validate([
            'name' => 'sometimes|string|unique:sponsors,name',
            'sponsor_type' => 'sometimes|string|in:cash,credit'
        ]);

        $sponsor->update($fields);

        return response()->json([
            'status' => 'success',
            'message' => 'Sponsor updated successfully!',
            'updated fields' => array_keys($fields)
        ], 201);

    }

    public function destroy($id){
        $sponsor  = Sponsor::findOrFail($id);

        $sponsor->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'sponsor deleted successfully!'
        ], 200);
    }
    public function index(){
        $sponsors = Sponsor::all();

        return response()->json([
            'status' => true,
            'Sponsors' => $sponsors,
            'count' => $sponsors->count()

        ], 200);

    }

}
