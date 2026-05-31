<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use Illuminate\Support\Str; 
use App\Models\CheckIn;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function makePayment(Request $request, $checkinID){
        $User_id = $request->user()->id;
        $checkInData = CheckIn::findOrFail($checkinID);

        $request->validate([
            'amount' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'payment_method' => 'required|in:cash_payment,msamaha,bill,credit_bill'
        ]);
        
        try{
            DB::beginTransaction();
            
            $data = [];
            $totalAmount = 0;
            if(isset($request->discount)){
                $totalAmount = $request->amount + $request->discount;
            }
            $totalAmount = $request->amount;

            $item_price = DB::table('item_prices')->join('items', 'item_prices.item_id', '=', 'items.id')
                         ->where('item_prices.item_id', $checkInData->item_id)->value('item_prices.price');
            $transactionType = DB::table('sponsors')->where('id', $checkInData->sponsor_id)->value('sponsor_type');

            if(!$totalAmount === $item_price){
            return response()->json([
                'status' => 'failed',
                'message' => 'amount should be equal to item price'
            ], 400);
            }

            $transactionId = Str::upper(Str::random(25));
            
            

            $payment = Payment::create([
                'check_in_id' => $checkInData->id,
                'sponsor_id' => $checkInData->sponsor_id,
                'clinic_id' => $checkInData->clinic_id,
                'doctor_id' => $checkInData->doctor_id,
                'patient_id' => $checkInData->patient_id,
                'item_id' => $checkInData->item_id,
                'user_id' =>  $User_id,
                'transaction_type' => $transactionType,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'payment_status' => 'paid',
                'transaction_id' => $transactionId,
                'discount' => $request->discount ?? null,
            ]);

            $paymentData = DB::table('payments')->join('check_ins', 'payments.check_in_id', '=', 'check_ins.id')
                            ->join('sponsors', 'payments.sponsor_id', '=', 'sponsors.id')
                            ->join('clinics', 'payments.clinic_id', '=', 'clinics.id')
                            ->join('users as doctors', 'payments.doctor_id', '=', 'doctors.id')
                            ->join('users as cashiers', 'payments.user_id', '=', 'cashiers.id')
                            ->join('patients', 'payments.patient_id', '=', 'patients.id')
                            ->join('items', 'payments.item_id', '=', 'items.id')
                            ->join('item_prices', 'item_prices.item_id', '=', 'items.id')
                            ->where('payments.id', $payment->id)
                            ->select(
                                'payments.id as payment_id',
                                'check_ins.id as checkin_id',
                                'patients.id as patient_number',
                                'patients.full_name as patient_name',
                                'patients.date_of_birth as patient_dob',
                                'patients.phone as patient_phone',
                                'patients.gender as gender',
                                'sponsors.name as sponsor_name',
                                'payments.transaction_type as transaction_type',
                                'clinics.name as clinic_name',
                                'doctors.name as doctor_name',
                                'items.name as item_name',
                                'item_prices.price as item_price',
                                'payments.amount as paid_amount',
                                'payments.discount as discount',
                                'payments.payment_status as status',
                                'payments.payment_method as payment_method',
                                'payments.transaction_id as transaction_id',
                                'cashiers.name as cashier_name',
                                'payments.created_at as payment_datetime'



                            )->first();
            $patientAge = Carbon::parse($paymentData->patient_dob)->age;
            $data[] = [
                    'Payment_id' => $paymentData->payment_id,
                    'Check_in_id' => $paymentData->checkin_id,
                    'Patient Number' => $paymentData->patient_number,
                    'Patient Name' => $paymentData->patient_name,
                    'Patient Age'   => $patientAge,
                    'Patient phone'  => $paymentData->patient_phone,
                    'Patient Gender'  => $paymentData->gender,
                    'Sponsor'      => $paymentData->sponsor_name,
                    'Transaction type' => $paymentData->transaction_type,
                    'Payment Method'  => $paymentData->payment_method,
                    'Clinic'      => $paymentData->clinic_name,
                    'Doctor'       => $paymentData->doctor_name,
                    'Item name'    => $paymentData->item_name,
                    'Price'        => $paymentData->item_price,
                    'Discount'     => $paymentData->discount,
                    'Total Paid Amount' => $paymentData->paid_amount,
                    'Status'      => $paymentData->status,
                    'Transation ID' => $paymentData->transaction_id,
                    'Payment datetime' => $paymentData->payment_datetime,
                    'Cashier name'   => $paymentData->cashier_name




            ];


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment processed successfully!',
                'data' => $data
            ], 200);

        }

        catch (Exception $e){
            DB::rollBack();

            Log::error('payment-error' . $e->getMessage());

            return response()->json([
                'status' => 'failed',
                'message' => 'something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }

    }
}
