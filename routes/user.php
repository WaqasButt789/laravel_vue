<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UpdateController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/




header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: *');
Route::post('/signup',[UserController::class,'signUp'])->middleware('EmailExists');
Route::post('/login',[UserController::class,'logIn'])->middleware('VerifiedEmail');
Route::post('/logout',[UserController::class,'logOut'])->middleware('JwtAuth');
Route::post('/updateuser',[UserController::class,'updateUser'])->middleware('JwtAuth');
Route::get('/verify/{email}/{token}',[UpdateController::class,'updateData']);
Route::any('/storage/public/profilepictures/{filename}',[UserController::class,'hitProfileLink']);
Route::post('/getprofile',[UserController::class,'getProfileData'])->middleware('JwtAuth');

