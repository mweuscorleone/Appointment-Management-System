<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ConsultationType;

class ConsultationTypeController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255|unique:consultation_types'
        ],
    [
        'name.unique' => 'consultation type name is already exist please try again!'
    ]);

    $consultationType = ConsultationType::create([
                        'name' => $request->name,
                        'status' => $request->status ?? 'active'
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'consultation type created successfully!',
        'consulatation type' => $consultationType
    ], 201);
    }

    public function update(Request $request, $id){
        $consultationType = ConsultationType::findOrFail($id);

        $fields = $request->validate([
            'name' => 'sometimes|string|max:255|unique:consultation_types,name',
            'status' =>  'sometimes|string|in:active,inactive'
        ],
        [
            'name.unique' => 'consultation type already exist please try again!',
            'status.in'  => 'status must be active or inactive'
        ]);

        $consultationType->update($fields);

        return response()->json([
            'status' => 'success',
            'message' => 'consultation type updated successfully!',
            'updated fields' => array_keys($fields)
        ], 200);
    }

    public function destroy($id){
        $consultationType = ConsultationType::findOrFail($id);

        $consultationType->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'consultation type deleted successfully!'
        ], 200);
    }

    public function index(){
        $consultationTypes = ConsultationType::all();

        return response()->json([
            'status' => true,
            'data'  => $consultationTypes,
            'count' => $consultationTypes->count()
        ], 200);
    }
}
