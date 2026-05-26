<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clinic;
use Carbon\Carbon;

class ClinicController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|unique:clinics,name',
            'location' => 'nullable|string'
        ],
        [
            'name.unique' => 'clinic already exists'
        ]);

        $clinic = Clinic::create([
            'name' => $request->name,
            'location' => $request->location ?? null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'clinic created successfully!',
            'clinic'  => $clinic
        ], 201);
    }

    public function update(Request $request, $id){
        $clinic = Clinic::findOrFail($id);

       $fields = $request->validate([
            'name' => 'sometimes|string',
            'location' => 'nullable|string'
       ]);

       $clinic->update($fields);

       return response()->json([
        'status' => 'success',
        'message' => 'clinic updated successfully!',
        'updated fields' => array_keys($fields)
       ], 200);
    }

    public function destroy($id){
        $clinic = Clinic::findOrFail($id);

        $clinic->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'clinic deleted successfully!'
        ], 200);
    }

    public function index(){
        $clinics = Clinic::all();
        $data = [];

        foreach($clinics as $clinic){
            $data[] = [
                'ClinicID' => $clinic->id,
                'Clinic Name' => $clinic->name,
                'Clinic Location' => $clinic->location,
                'created at' => Carbon::parse($clinic->created_at)->format('Y-m-d:H:m:s'),
                'updated at' => Carbon::parse($clinic->updated_at)->format('Y-m-d:H:m:s')

            ];
        }
        $total_clinics = count($data);

        return response()->json([
            'status' => true,
            'clinics' => $data,
            'count' => $total_clinics

        ], 200);
    }
}
