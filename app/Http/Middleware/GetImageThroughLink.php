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

     /**
     * checking for privte images if image is belonging to the
     * user who is login than it will show the image or if the email of the user
     * who is login exists in the embeded array of allowed emails than it will
     * show the image to the user else it
     * will return a message of not allowed
     */
    public function isPrivate($conn,$key,$search) {

        if($key!=null)
        {
            $data=$conn->get_connection('users')->findOne(['token'=>$key]);
            if($data!=null){
                $email=$data->email;
                $check=$conn->get_connection('images')->findOne( ['image' => $search,'Allowed_Emails'=> $email]);
                if($check!=null) {  return true;    }
                $uid=$data->_id;
                $imagedata=$conn->get_connection('images')->findOne(['user_id'=>$uid,'image'=>$search]);
                if($imagedata!=null) {  return true;    }
            }
        }
        else{   return false;   }
    }

    /**
     * checking for hidden images if image is belonging to the
     * user who is login than it will show the image else it
     * will return a message of not allowed
     */

    public function checkHidden($conn,$key,$search) {

        if($key!=null) {
            $data=$conn->get_connection('users')->findOne(['token'=>$key]);
            if($data!=null){
                $uid=$data->_id;
                $imagedata=$conn->get_connection('images')->findOne(['user_id'=>$uid,'image'=>$search]);
                if($imagedata!=null) {  return true;    }
                else {  return false;   }
            }
            else {  return false;   }
        }
        else {  return false;   }
    }
    public function handle(Request $request, Closure $next)
    {
        $conn=new DataBaseConnection();
        $key=$request->bearerToken();
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){ $url = "https://";}
        else{   $url = "http://";   }
        $url.= $_SERVER['HTTP_HOST'];
        $search=$url."/api/storage/public/images".'/'.$request->filename;
        $imagedata=$conn->get_connection('images')->findOne(['image'=>$search]);
        if($imagedata->accessor == "public") {  return $next($request); }
        else if($imagedata->accessor == "private")
        {
            $check=$this->isPrivate($conn,$key,$search);
            if($check==true) {  return $next($request); }
            else {  return response(["message"=>"you are not allowed"],405);    }
        }
        else if($imagedata->accessor == "hidden")
        {
            $checforhidden=$this->checkHidden($conn,$key,$search);
            if($checforhidden == true) {    return $next($request);  }
            else {  return response(["message"=>"not allowed its hidden"],405); }
        }
    }
}
