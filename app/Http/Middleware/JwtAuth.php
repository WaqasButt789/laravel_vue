<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\DataBaseConnection;

class JwtAuth
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
        // $key=$req->token;
        $key =$req->bearerToken();
        
        $data=$conn->get_connection('users')->findOne(['token'=>$key]);



        if($data!=NULL){
            $data['db']=$conn;
            return $next($req->merge(["data"=>$data]));
        }
        else{
            return response(["message"=>"you are not login"]);
        }
    }
}
