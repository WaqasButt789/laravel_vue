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
        $key=$request->bearerToken();

        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){ $url = "https://";}
        else{$url = "http://";}
        $url.= $_SERVER['HTTP_HOST'];
        $search=$url."/api/storage/public/images".'/'.$request->filename;

        $imagedata=$conn->get_connection('images')->findOne(['image'=>$search]);
        if($imagedata->accessor == "public") {
            return $next($request);
        }
        else if($imagedata->accessor == "private")
        {
            if($key!=null) {

                $data=$conn->get_connection('users')->findOne(['token'=>$key]);
                $email=$data->email;
                $uid=$data->_id;
                $check=$conn->get_connection('images')->findOne( ['image' => $search,'Allowed_Emails'=> $email]);
                if($check!=null) {
                    return $next($request);
                }
                $imagedata=$conn->get_connection('images')->findOne(['user_id'=>$uid,'image'=>$search]);
                if($image) {

                }


            }
            else{
                return response(["message"=>"you are not allowed"],405);
            }
        }
        else if($imagedata->accessor == "hidden"){
            return response(["message"=>"not allowed its hidden"],405);
        }

    }
}
