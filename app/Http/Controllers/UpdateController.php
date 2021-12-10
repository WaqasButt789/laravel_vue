<?php

namespace App\Http\Controllers;

use App\Services\DataBaseConnection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    /**
     * updating Email_verified_at field
     */
    public function updateData($email,$token)
    {
        $conn = new DataBaseConnection();
        $data=$conn->get_connection('users')->findOne([
            'email' => $email,
            'email_token' => (int)$token
        ]);
        if($data!==null)
            {
                $conn->get_connection('users')->updateOne(["email"=>$email],
                ['$set'=>['email_verified' => true]]);
                return response(['message'=>'Your Email has been Verified']);
            }
        else
            {
                return response(['message' => 'your email is not verified..!!!']);
            }
    }
}
