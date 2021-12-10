<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;


Route::group(['middleware'=>'JwtAuth'],function () {

Route::post('/uploadimage',[ImageController::class,'uploadImage']);
Route::post('/deleteimage',[ImageController::class,'deleteImage'])->middleware('DeleteImageMiddleWare');
Route::post('/searchimage',[ImageController::class,'searchImages']);
Route::post('/listimages',[ImageController::class,'listImages']);
Route::post('/makepublic',[ImageController::class,'makePublic']);
Route::post('/makeprivate',[ImageController::class,'makePrivate']);
Route::post('/addemail',[ImageController::class,'addEmail'])->middleware('MakePrivateMiddleWare');
Route::post('/makehidden',[ImageController::class,'makeHidden']);
Route::post('/removeoneemail',[ImageController::class,'removeOneEmail']);
Route::post('/generatelink',[ImageController::class,'getLink']);
});


