<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use Carbon\Carbon;
use App\Models\Sponsor;

class PatientController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'full_name' => 'required|string|max:255',
            'sponsor_id' => 'required|numeric|exists:sponsors,id',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|in:male,female',
            'phone' => ['required',
                         'regex:/^(07|06)[0-9]{8}$/',
                         'unique:patients,phone'],
            'address' => 'nullable|string'
        ],
        [   'phone.regex' => 'Phone number must start with 07 0r 06 and should not exceed 10 digits',
            'phone.unique' => 'patient with the same phone number is already exist please try again!',
            'sponsor_id.exists' => 'select sponsor with ID ' . $request->sponsor_id . ' is not exist please, try again!'
        ]);

        $patient = Patient::create([
                    'full_name' => $request->full_name,
                    'sponsor_id' => $request->sponsor_id,
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
            'sponsor_id' => 'sometimes|numeric',
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
