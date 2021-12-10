<?php

namespace App\Http\Controllers;

use App\Jobs\QueueJob;
use App\Services\DataBaseConnection;
use App\Services\JwtService;
use App\Services\Mail\TestMail;
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
            $base64_string =  $req->file;
            $extension = explode('/', explode(':', substr($base64_string, 0, strpos($base64_string, ';')))[1])[1]; // .jpg .png .pdf
            $replace = substr($base64_string, 0, strpos($base64_string, ',')+1);
            $image = str_replace($replace, '', $base64_string);
            $image = str_replace(' ', '+', $image);
            $fileName = time().'.'.$extension;

            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
                $url = "https://";
            }
            else{
                $url = "http://";
            }
            $url.= $_SERVER['HTTP_HOST'];
            $pathD=$url."/api/storage/public/images/".$fileName;
            $path=storage_path('app\\public\\images').'\\'.$fileName;
            file_put_contents($path,base64_decode($image));
        }

        $conn->get_connection('users')->insertOne([
            'name' => $name,'email' => $email,'password' => $password,'file' => $pathD,'age' => $age,
            'token'=>NULL,'email_token' => $token,'email_verified' => false,'status'=> 0 ,
        ]);
        $this->sendmail($email,$token);
        return response()->json(["message"=>"plese verify your email to proceed further"]);
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
            'body' => 'http://127.0.0.1:8000/user/verify/'.$mail.'/'.$token
        ];
       dispatch(new QueueJob($mail,$details));
        return "Email Sent Succesfully";
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
        if($req->file('file') != NULL)
        {
            $original_path=$req->file('file')->store('images');
            dd($original_path);
            $path=$_SERVER['HTTP_HOST']."/api/storage/".$original_path;
            $data['file'] =$path;
        }
        if($req->name != NULL){$data['name'] = $req->name;}
        if($req->password != NULL){$data['password'] = Hash::make($req->password);}
        if($req->age != NULL){$data['age'] = $req->age;}
        if($req->email != NULL){$data['email'] = $req->email;}
        if(count($data) != 0) {
            $conn->get_connection('users')->updateOne([ '_id' => $uid ],[ '$set' => $data]);
            return response()->json(["messsage" => "user data updated successfuly"]);
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
        return response(["message" => "logout successfuly"]);
    }
}
