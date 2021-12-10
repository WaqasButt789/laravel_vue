<?php

namespace App\Http\Controllers;

use App\Services\Mail\TestMail;
use App\Services\DataBaseConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgetPassword extends Controller
{

    public function forget(Request $req) {
        $email=$req->email;
        $conn= new DataBaseConnection();
        $key = rand(100,1000);
        $details=[
            'title' => 'Please Use This OTP : '. $key,
            'body' => ' '
        ];
        $conn->get_connection('users')->updateOne(
            [ 'email'=> $email ],
            [ '$set' => ['email_token'=>$key]]
        );
        Mail::to($email)->send(new TestMail($details));
        return response(["message"=>"We have sent an OTP to your registered email Please verify yourself"]);
    }
/**
 * function which will get new password and replace it with old one
 */
    public function newPassword(Request $req) {
        $email=$req->email;
        $newpassword=$req->newpassword;
        $conn = new DataBaseConnection();
        $pass=Hash::make($newpassword);
        $conn->get_connection('users')->updateOne(
            [ 'email'=> $email ],
            [ '$set' => ['password' => $pass]]
        );
        return response(['message' => 'Password changed successfuly']);
    }
}

