<?php

use App\Http\Controllers\ForgetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware'=>'VerifiedEmail'],function() {
    Route::post('/forgetpassword',[ForgetPassword::class,'forget']);
    Route::post('/newpassword',[ForgetPassword::class,'newPassword'])->middleware('CheckOtp');
});
