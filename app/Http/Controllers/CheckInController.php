<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CheckIn;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Sponsor;
use App\Models\Items;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;



class CheckInController extends Controller
{
    public function store(Request $request){
        $user_id = $request->user()->id;
        $request->validate([
            'patient_id' => 'required|numeric|exists:patients,id',
            'sponsor_id' => 'required|numeric|exists:sponsors,id',
            'doctor_id' => 'required|numeric|exists:users,id',
            'clinic_id' => 'required|numeric|exists:clinics,id',
            'item_id' => 'required|numeric|exists:items,id',
            
        ], 
        [
            'patient_id.exists' => "Patient with an id {$request->patient_id} do not exist please try again later!",
            'sponsor_id.exists' => "Sponsor with an id {$request->sponsor_id} do not exist please try again later!",
            'doctor_id.exists' => "Doctor with an id {$request->doctor_id} do not exist please try again later!",
            'clinic_id.exists' => "Clinic with an id {$request->clinic_id} do not exist please try again later!",
            'item_id.exists' => "Item with an id {$request->item_id} do not exist please try again later!"

        ]);
        $exists = CheckIn::where('patient_id', $request->patient_id)->latest()->first();
        if($exists){
            $maxCheckinTime = Carbon::parse($exists->created_at)->addHour(24);

            if(Carbon::now()->lessThan($maxCheckinTime)){
                 return response()->json([
                'status' => 'failed',
                'message' => 'Try again after ' . $maxCheckinTime->diffForHumans() . 
                      ', patient already checkedin with check in ID ' . $exists->id
            ],  400);

            }
        }
        

        $doctor = User::where('id', $request->doctor_id)->where('role', 'doctor')->first();

        if(!$doctor){
            return response()->json(['message' => 'selected employee is not a Doctor please try again'], 400);
        }
        $data = [];

    try{
        DB::beginTransaction();

        $checkIn = CheckIn::create([
            'patient_id' => $request->patient_id,
            'sponsor_id' => $request->sponsor_id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $request->clinic_id,
            'item_id'  => $request->item_id,
            'user_id' => $user_id
        ]);

        $checkinData = DB::table('check_ins')->join('patients', 'check_ins.patient_id', '=', 'patients.id')
                      ->join('sponsors', 'check_ins.sponsor_id', '=', 'sponsors.id')
                      ->join('items', 'check_ins.item_id', '=', 'items.id')
                      ->join('item_prices', 'item_prices.item_id', '=', 'items.id')
                      ->join('clinics', 'check_ins.clinic_id', '=', 'clinics.id')
                      ->join('users', 'check_ins.user_id', '=', 'users.id')->where('check_ins.id', $checkIn->id)
                      ->select(
                        'check_ins.id as check_in_id',
                        'patients.id as patient_id',
                        'patients.full_name as patient_name',
                        'sponsors.name as sponsor_name',
                        'patients.date_of_birth as patient_date_of_birth',
                        'patients.gender as gender',
                        'clinics.name as clinic_name',
                        'items.name as item_name',
                        'item_prices.price as item_price',
                        'users.name as checkin_by',
                        'check_ins.created_at as check_in_datetime'
                      )
                      ->first();

        $patientAge = Carbon::parse($checkinData->patient_date_of_birth)->age;
        
        $data[] = [
            'Checkin ID' => $checkinData->check_in_id,
            'Patient Number' => $checkinData->patient_id,
            'Patient Name' => $checkinData->patient_name,
            'Sponsor' => $checkinData->sponsor_name,
            'Patient age' => $patientAge,
            'Gender'  => $checkinData->gender,
            'Clinic' => $checkinData->clinic_name,
            'Doctor' => $doctor->name,
            'Item name' => $checkinData->item_name,
            'Item price' => $checkinData->item_price,
            'CheckedIn by' => $checkinData->checkin_by,
            'CheckIn datetime' => $checkinData->check_in_datetime,
            'CheckIn expired in' => Carbon::parse($checkinData->check_in_datetime)->addHours(24)->diffForHumans()


        ];
        DB::commit();
        return response()->json([
            'status' => 'success',
            'message' => 'Patient checked in successfully!',
            'check in details' => $data

        ], 200
    );
   

    }

    catch (Exception $e){
        DB::rollBack();

        Log::error('check-in-error' . $e->getMessage());

        return response()->json([
            'status' => 'failed',
            'message' => 'something went wrong!',
            'error'   => $e->getMessage()
        ], 500);
    }
    }


    public function getCheckedInPatients(){

       $checkedInPatients = DB::table('check_ins')->join('patients', 'check_ins.patient_id', '=', 'patients.id')
                      ->join('sponsors', 'check_ins.sponsor_id', '=', 'sponsors.id')
                      ->join('items', 'check_ins.item_id', '=', 'items.id')
                      ->join('users as doctors', 'check_ins.doctor_id', '=', 'doctors.id')
                      ->join('item_prices', 'item_prices.item_id', '=', 'items.id')
                      ->join('clinics', 'check_ins.clinic_id', '=', 'clinics.id')
                      ->join('users as checkedUsers', 'check_ins.user_id', '=', 'checkedUsers.id')
                      ->select(
                        'check_ins.id as check_in_id',
                        'patients.id as patient_id',
                        'patients.full_name as patient_name',
                        'sponsors.name as sponsor_name',
                        'patients.date_of_birth as patient_date_of_birth',
                        'patients.gender as gender',
                        'clinics.name as clinic_name',
                        'doctors.name as doctor',
                        'items.name as item_name',
                        'item_prices.price as item_price',
                        'checkedUsers.name as checkin_by',
                        'check_ins.created_at as check_in_datetime'
                      )
                      ->get()
                    ->map(function ($checkedInPatient){
                        $expired = Carbon::parse($checkedInPatient->check_in_datetime)->addHours(24)->isPast();
                        
                        if(!$expired){
                            $patientAge = Carbon::parse($checkedInPatient->patient_date_of_birth)->age;
                             return [

                            'Checkin ID' => $checkedInPatient->check_in_id,
                            'Patient Number' => $checkedInPatient->patient_id,
                            'Patient Name' => $checkedInPatient->patient_name,
                            'Sponsor' => $checkedInPatient->sponsor_name,
                            'Patient age' => $patientAge,
                            'Gender'  => $checkedInPatient->gender,
                            'Clinic' => $checkedInPatient->clinic_name,
                            'Doctor' => $checkedInPatient->doctor,
                            'Item name' => $checkedInPatient->item_name,
                            'Item price' => $checkedInPatient->item_price,
                            'CheckedIn by' => $checkedInPatient->checkin_by,
                            'CheckIn datetime' => $checkedInPatient->check_in_datetime,
                            'CheckIn expired in' => Carbon::parse($checkedInPatient->check_in_datetime)->addHours(24)->diffForHumans()


                        ];

                        }
                        else{
                            return response()->json([
                                'status' => 'failed',
                                'message' => 'no current checkedIn patent'
                            ], 400);
                        }
                       
                    });
        if(!$checkedInPatients){
            return response()->json([
                    'status' => 'failed',
                    'message' => 'no current checkedIn patent'
                     ], 400);
        }
        else{ 
             return response()->json([
                    'status'  => true,
                    'checked in patients' => $checkedInPatients,
                    'Total CheckedIn Patient' => $checkedInPatients->count()
                ], 200);



        }


      
        
        
        
            

    }
}
