<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\ItemPriceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ConsultationTypeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
//THESE ROUTES ONLY ADMIN CAN ACCESS
Route::middleware(['auth:api', 'role:admin'])->group(function (){

    //USER MANAGEMENT
    Route::post('create/user', [UserController::class, 'store']);
    Route::put('update/user/{id}', [UserController::class, 'update']);
    Route::delete('delete/user/{id}', [UserController::class, 'destroy']);
    Route::get('users/list', [UserController::class, 'index']);

    //CLINIC MANAGEMENT
    Route::post('create/clinic', [ClinicController::class, 'store']);
    Route::put('update/clinic/{id}', [ClinicController::class, 'update']);
    Route::delete('delete/clinic/{id}', [ClinicController::class, 'destroy']);
    Route::get('clinics/list', [ClinicController::class, 'index']);

    //ITEM MANAGEMENT 
    Route::post('create/item', [ItemController::class, 'store']);
    Route::put('update/item/{id}', [ItemController::class, 'update']);
    Route::delete('delete/item/{id}', [ItemController::class, 'destroy']);
    Route::get('items/list', [ItemController::class, 'indexx']);

    //ITEM CATEGORIES MANAGEMENT
    Route::post('create/category', [ItemCategoryController::class, 'store']);
    Route::put('update/category/{id}', [ItemCategoryController::class, 'update']);
    Route::delete('delete/category/{id}', [ItemCategoryController::class, 'destroy']);
    Route::get('categories/list', [ItemCategoryController::class, 'index']);

    //SPONSOR MANAGEMENT
    Route::post('create/sponsor', [SponsorController::class, 'store']);
    Route::put('update/sponsor/{id}', [SponsorController::class, 'update']);
    Route::delete('/delete/sponsor/{id}', [SponsorController::class, 'destroy']);
    Route::get('sponsors/list', [SponsorController::class, 'index']);

    //ITEM PRICE MANAGEMENT
    Route::post('item/price', [ItemPriceController::class, 'store']);


    //CONSULTATION TYPES MANAGEMENT
    Route::post('create/consultation-type', [ConsultationTypeController::class, 'store']);
    Route::put('update/consultation-type/{id}', [ConsultationTypeController::class, 'update']);
    Route::delete('delete/consultation-type/{id}', [ConsultationTypeController::class, 'destroy']);
    Route::get('consultation-types/list', [ConsultationTypeController::class, 'index']);


});

Route::middleware(['auth:api', 'role:reception,admin,doctor'])->group(function (){

    //PATIENT MANAGEMENT ROUTES
    Route::post('register/patient', [PatientController::class, 'store']);
    Route::put('update/patient/{id}', [PatientController::class, 'update']);
    Route::delete('delete/patient/{id}', [PatientController::class, 'destroy']);
    Route::get('patients/list', [PatientController::class, 'index']);

    //APPOINTMENT MANAGEMENT ROUTES
    Route::post('create/appointment', [AppointmentController::class, 'store']);

    //PATIENT CHECKIN
    Route::post('patient/check-in', [CheckInController::class, 'store']);
    Route::get('checked-in/patients', [CheckInController::class, 'getCheckedInPatients']);

    //PATIENT PAYMENTS 
    Route::post('patient/payment/{checkId}', [PaymentController::class, 'makePayment']);




});
    //ONLY ADMIN AND DOCTOR CAN ACCESS THESE ROUTES
Route::middleware(['auth:api', 'role:admin,doctor'])->group(function (){
    //APPOINTMENT MANAGEMENT
    Route::patch('cancel/appointment/{id}', [AppointmentController::class, 'CancelAppointment']);
    Route::put('update/appointment/{id}', [AppointmentController::class, 'update']);
    Route::delete('delete/appointment/{id}', [AppointmentController::class, 'destroy']);
    Route::get('appointments/list', [AppointmentController::class, 'index']);
    Route::get('my-appointment/list', [AppointmentController::class, 'viewAppointments']);
});

Route::post('user/login', [AuthController::class, 'login']);
Route::post('user/logout', [AuthController::class, 'logout']);
Route::post('password/reset/token', [AuthController::class, 'passwordResetToken']);
Route::post('reset/password', [AuthController::class, 'resetPassword']);
Route::post('create/key', [ApiKeyController::class, 'store']);

//PROTECTED ROUTE FOR THIRD PART APPICATION
Route::middleware('api.key')->group(function (){
   Route::get('patients/list', [PatientController::class, 'index']);
});
 