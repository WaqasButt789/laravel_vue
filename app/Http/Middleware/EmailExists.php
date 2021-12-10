<?php

namespace App\Http\Middleware;

use App\Services\DataBaseConnection;
use Closure;
use Illuminate\Http\Request;

class EmailExists
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
        $email=$req->email;
        $conn=new DataBaseConnection();
        $data=$conn->get_connection('users')->findOne(['email'=>$email]);
        if($data!==null){
            return response(["message" => "user already exists"]);
        }
        else{
            return $next($req);
        }
    }
}
