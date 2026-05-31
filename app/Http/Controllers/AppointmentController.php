<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Patient;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'doctor_id' => 'required|numeric|exists:users,id',
            'patient_id' => 'required|numeric|unique:appointments,patient_id|exists:patients,id',
            'clinic_id' => 'required|numeric|exists:clinics,id',
            'appointment_date' => 'required|date|after:' . Carbon::now()->addHour()->toDateTimeString(),
            'status'  => 'nullable|string|in:scheduled,canceled,postponed,served'
        ],
        [
            'doctor_id' => "provided doctor id {$request->doctor_id} do not exist please try again",
            'patient_id' => "provided patient id {$request->patient_id} do not exist please try again",
            'clinic_id' => "provided clinic id {$request->clinic_id} do not exist please try again",
            'appointment_date.after' => 'Appointment date must be scheduled at least 1 hour from now in advance',
            'status.in' => 'appointment status should be in this list (sheduled,cancelled,postponed,completed,not attended)',
            'patient_id.unique' => 'appointment for ' . DB::table('patients')->where('id', $request->patient_id)->value('full_name') . 
                                    ' has already been made with an Appointment ID ' .  DB::table('appointments')->where('patient_id', $request->patient_id)->value('id')
        ]);
        $doctor = User::where('id', $request->doctor_id)->where('role', 'doctor')->first();

        if(!$doctor){
            return response()->json([
                'message' => "selected employee with an Id {$request->doctor_id} is not a Doctor please try again!"
            ], 400);
        }
        
        $data = DB::transaction(function() use($doctor, $request){
                $appointment = Appointment::create([
                'doctor_id' => $doctor->id,
                'patient_id' => $request->patient_id,
                'clinic_id'  => $request->clinic_id,
                'appointment_date' => $request->appointment_date,
                'status' => $request->status ?? 'scheduled'
                    ]);
            
                $patient = Patient::where('id', $appointment->patient_id)->value('full_name');
                $doctor = User::where('id', $appointment->doctor_id)->value('name');
                $clinic = Clinic::where('id', $appointment->clinic_id)->value('name');
                $appointmentDate = Carbon::parse($appointment->appointment_date)->format('l d M Y H:i');
                
                return [
                'Appointment ID' => $appointment->id,
                'Doctor' => $doctor,
                'Patient Name' => $patient,
                'Clinic' => $clinic,
                'Appointment Date' => $appointmentDate,
                'Status' => $appointment->status
            ];
            

        });
        
        return response()->json([
            'status' => 'success',
            'message' => 'appointment created successfull',
            'appointment' => $data
        ], 201);


    }
    public function cancelAppointment(Request $request, $id){
        $appointment = Appointment::findOrFail($id);

        $appointment->update([
            'status' => 'cancelled',
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'appointment cancelled succesfully!'
        ], 200);


    }

    public function update(Request $request, $id){
        $appointment = Appointment::findOrFail($id);

        $fields = $request-> validate([
            'doctor_id' => 'sometimes|numeric|exists:users,id',
            'patient_id' => 'sometimes|numeric|exists:patients,id',
            'clinic_id' => 'sometimes|numeric|exists:clinics,id',
            'appointment_date' => 'sometimes|date|after:' . Carbon::now()->addHour()->toDateTimeString(),
            'status' => 'nullable|string|in:scheduled,cancelled,completed,postponed,not attended'
        ],
        [
            'doctor_id.exists' => "Doctor with ID {$request->doctor_id} is not exist please try again later",
            'patient_id.exists' => "Patient with ID {$request->patient_id} is not exist please try again later",
            'clinic_id.exists' => "Clinic with ID {$request->clinic_id} is not exist please try again later",
            'appointment_date.after' => 'Appointment date must be scheduled at least 1 hour from now in advance',
            'status.in' => 'appointment status should be in this list (sheduled,cancelled,postponed,completed,not attended)'
            
        ]);

        $appointment->update([
            'doctor_id' => $fields['doctor_id'] ?? $appointment->doctor_id,
            'patient_id' => $fields['patient_id'] ?? $appointment->patient_id,
            'clinic_id' => $fields['clinic_id']  ?? $appointment->clinic_id,
            'appointment_date' => $fields['appointment_date'] ?? $appointment->appointment_date,
            'status' => $fields['status'] ?? $appointment->status
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'appointment updated successfully!',
            'updated fields' => array_keys($fields)
        ], 200);
    }

    public function destroy($id){
        $appointment = Appointment::findOrFail($id);

        $appointment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'appointment removed successfully!'
        ], 200);
    }

    public function index(){
        $appointments = Appointment::with('doctor', 'patient', 'clinic')->get();

        $data = $appointments->map(function ($appointment){
            $patientAge = Carbon::parse($appointment->patient->date_of_birth)->age;
            $appointmentDate = Carbon::parse($appointment->appointment_date)->format('l d M Y : H:i');
            return [
                'Appointment ID' => $appointment->id,
                'Doctor'  => $appointment->doctor->name,
                'Patient' => $appointment->patient->full_name,
                'Patient Age' => $patientAge,
                'Clinic'  => $appointment->clinic->name,
                'Appointment Date' => $appointmentDate,
                'Status'  => $appointment->status

            ];
        });

        return response()->json([
            'status'  => true,
            'Appointments' => $data,
            'Count' => $data->count()
        ], 200);
    }
    public function viewAppointments(Request $request){
        $userId = $request->user()->id;
        $appointments = Appointment::with('doctor', 'patient', 'clinic')->where('doctor_id', $userId)->get();

        $data = $appointments->map(function ($appointment){
            $patientAge = Carbon::parse($appointment->patient->date_of_birth)->age;
            $appointmentDate = Carbon::parse($appointment->appointment_date)->format('l d M Y : H:i');
            return [
                'Appointment ID' => $appointment->id,
                'Doctor'  => $appointment->doctor->name,
                'Patient' => $appointment->patient->full_name,
                'Patient Age' => $patientAge,
                'Clinic'  => $appointment->clinic->name,
                'Appointment Date' => $appointmentDate,
                'Status'  => $appointment->status

            ];
        });

        return response()->json([
            'status'  => true,
            'My Appointments' => $data,
            'Count' => $data->count()
        ], 200);

    }

}
