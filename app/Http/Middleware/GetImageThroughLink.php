<?php

namespace App\Http\Middleware;

use App\Services\DataBaseConnection;
use Closure;
use Illuminate\Http\Request;

class GetImageThroughLink
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $conn=new DataBaseConnection();
       // $image_id=new \MongoDB\BSON\ObjectId($req->image_id);
        $key=$request->token;

        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){ $url = "https://";}
        else{$url = "http://";}
        $url.= $_SERVER['HTTP_HOST'];
        $search=$url."/api/storage/public/images".'/'.$request->filename;
        $data=$conn->get_connection('images')->findOne(['image'=>$search]);
        if($data->accessor == "public") {
            return $next($request);
        }
        else if($data->accessor == "private")
        {
            if($key!=null) {
                $data=$conn->get_connection('users')->findOne(['token'=>$key]);
                $email=$request->data->email;
                $check=$conn->get_connection('images')->findOne( ['image' => $search,'emails'=> $email]);
                dd($check);
            }
            else{
                return response(["message"=>"you are not allowed"],405);
            }
        }
        else if($data->accessor == "hidden"){
            return response(["message"=>"not allowed its hidden"],405);
        }

    }
}
