<?php

namespace App\Http\Middleware;

use App\Services\DataBaseConnection;
use Closure;
use Illuminate\Http\Request;

class VerifiedEmail
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $req, Closure $next)
    {

        $conn=new DataBaseConnection();
        $email=$req->email;
        $data=$conn->get_connection('users')->findOne(["email"=>$email]);

        if($data!=NULL){
            if($data->email_verified == true) {
                return $next($req);
            }
            else {
                return response(["message"=>"Please Verify Your Email To Proceed Further"]);
            }
        }
        else{

            return response(["message"=>"invalid credentials"]);
        }
    }
}
