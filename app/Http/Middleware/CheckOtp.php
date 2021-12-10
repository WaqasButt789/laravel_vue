<?php

namespace App\Http\Middleware;
use App\Services\DataBaseConnection;
use Closure;
use Illuminate\Http\Request;

class CheckOtp
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
        $token=(int)$req->token;
        $email=$req->email;
        $conn = new DataBaseConnection();
        $data=(array)$conn->get_connection('users')
            ->findOne(['email' => $email , 'email_token' => $token]);
        if($data!=NULL) {
        return $next($req);
        }
        else {
            return response(['message' => 'Please Provide a Valid OTP']);
        }
    }
}
