<?php

namespace App\Http\Middleware;

use App\Services\DataBaseConnection;
use Closure;
use Illuminate\Http\Request;

class MakePrivateMiddleWare
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
        $image_id=new \MongoDB\BSON\ObjectId($req->image_id);

        $check1=$conn->get_connection('users')->findOne(['email'=>$req->email,'email_verified'=>true]);//email registered or not
        $check2=$conn->get_connection('images')->findOne(['_id'=>$image_id,'Allowed_Emails'=>$req->email]);//if email not exists than allow
        if($check1!=null) {
            if($check2==null) {
                return $next($req);
            }
        }
        else{
            return response(["message"=>"invalid Data"],422);
        }
    }
}
