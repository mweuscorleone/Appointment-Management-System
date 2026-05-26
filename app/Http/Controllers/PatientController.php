<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use Carbon\Carbon;

class PatientController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|in:male,female',
            'phone' => 'required|string|unique:patients,phone',
            'address' => 'nullable|string'
        ],
        [
            'phone.unique' => 'patient with the same phone number is already exist please try again!' 
        ]);

        $patient = Patient::create([
                    'full_name' => $request->full_name,
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'phone' => $request->phone,
                    'address' => $request->address ?? null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Patient Registered Successfully!',
            'patient' => $patient
        ], 201);

    }

    public function update(Request $request, $id){
        $patient = Patient::findOrFail($id);

        $fields = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|string|in:male,female',
            'phone' => 'sometimes|string|unique:patients,phone',
            'address' => 'nullable|string'
        ]);

        $patient->update($fields);

        return response()->json([
            'status' => 'success',
            'message' => 'patient details updated successfully!',
            'updated fields are' => array_keys($fields)
        ], 200);
    }

    public function destroy($id){
        $patient = Patient::findOrFail($id);

        $patient->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'patient deleted successfully!'
        ], 200);
    }

    public function index(){
        $patients = Patient::all();
        $data = [];

        foreach($patients as $patient){
            $age = Carbon::parse($patient->date_of_birth)->age;
            $data[] = [
                'patient number' => $patient->id,
                'patient name' => $patient->full_name,
                'age' => $age,
                'phone' => $patient->phone,
                'address' => $patient->address

            ];

        }
        $total_patients = count($data);

        return response()->json([
            'status' => true,
            'patients' => $data,
            'total patient' => $total_patients
        ], 200);
    }

}
