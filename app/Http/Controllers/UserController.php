<?php

namespace App\Http\Controllers;

use App\Helpers\Base64DecoderHelper;
use App\Jobs\QueueJob;
use App\Services\DataBaseConnection;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function signUp(Request $req)
    {
        $conn=new DataBaseConnection();
        $name=$req->name;
        $email=$req->email;
        $password=Hash::make($req->password);
        $age=$req->age;
        $token =$token = rand(100,1000);
        $fileName=null;
        if(!empty($req->file)){
            $file=Base64DecoderHelper::decodeBase64($req->file);
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
                $url = "https://";
            }
            else{
                $url = "http://";
            }
            $url.= $_SERVER['HTTP_HOST'];
            $pathD=$url."/user/storage/public/profilepictures/".$file[0];
            $path=storage_path('app\\public\\profilepictures').'\\'.$file[0];
            file_put_contents($path,base64_decode($file[1]));
        }
        $conn->get_connection('users')->insertOne([
            'name' => $name,'email' => $email,'password' => $password,'file' => $pathD,'age' => $age,
            'token'=>NULL,'email_token' => $token,'email_verified' => false,'status'=> 0 ,
        ]);
        $this->sendmail($email,$token);

        return response()->success();

        //return response()->json(["message"=>"plese verify your email to proceed further"]);
    }
    public function sendmail($mail,$token)
    {
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
            $url = "https://";
        }
        else{
            $url = "http://";
        }
        $url.= $_SERVER['HTTP_HOST'];
        $details=[
            'title' => 'Please Verify Your Email By clicking On Following Link',
            'body' => $url.'/user/verify/'.$mail.'/'.$token
        ];
       dispatch(new QueueJob($mail,$details));
    }

    public function logIn(Request $req){
        $conn=new DataBaseConnection();
        $token=new JwtService();
        $jwt=$token->getJwt();
        $email=$req->email;
        $data=$conn->get_connection('users')->find(['email'=>$email]);
        $objects = json_decode(json_encode($data->toArray(),true));
        $pass=$objects[0]->password;
        if(Hash::check($req->password, $pass)) {
            $conn->get_connection("users")->updateOne(
                ["email"=>$email],
                ['$set'=>['status' => 1 ,'token' => $jwt]
            ]);
            return response()->json(['access_token'=>$jwt , 'message'=> 'successfuly login']);
        }
        else {
            return response(["message"=>"invalid credentials"]);
        }
    }

    public function updateUser(Request $req) {
        $uid=$req->data->_id;
        $conn=new DataBaseConnection();
        $data=[];
        if($req->file != NULL)
        {
            $file=Base64DecoderHelper::decodeBase64($req->file);
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
                $url = "https://";
            }
            else{
                $url = "http://";
            }
            $url.= $_SERVER['HTTP_HOST'];
            $pathD=$url."/user/storage/public/profilepictures/".$file[0];
            $path=storage_path('app\\public\\profilepictures').'\\'.$file[0];
            file_put_contents($path,base64_decode($file[1]));
            $data['file'] =$pathD;
        }
        if($req->name != NULL){$data['name'] = $req->name;}
        if($req->password != NULL){$data['password'] = Hash::make($req->password);}
        if($req->age != NULL){$data['age'] = $req->age;}
        if($req->email != NULL){$data['email'] = $req->email;}
        if(count($data) != 0) {
            $conn->get_connection('users')->updateOne([ '_id' => $uid ],[ '$set' => $data]);
            return response()->success();

        }
        else{
            return response(["message"=>"No Data To Update"]);
        }
    }

    public function logOut(Request $req) {
        $conn=new DataBaseConnection();
        $uid=$req->data->_id;
        $conn->get_connection("users")->updateOne(
            ["_id"=>$uid],
            ['$set'=>['status' => 0 ,'token' => NULL]
        ]);
        return response()->success();
    }

    public function getProfileData(Request $req) {
        $data=$req->data->db->get_connection('users')->findOne(['_id'=>$req->data->_id]);
        return response([$data]);
    }

    public function hitProfileLink(Request $request, $filename){
        $headers = ["Cache-Control" => "no-store, no-cache, must-revalidate, max-age=0"];
        $path = storage_path("app/public/profilepictures".'/'.$filename);
         if (file_exists($path)) {
            return response()->download($path, null, $headers, null);
        }
        return response()->json(["error"=>"error downloading file"],400);
    }
}
