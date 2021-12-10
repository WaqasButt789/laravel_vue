<?php

namespace App\Http\Controllers;
use App\Services\DataBaseConnection;
use Illuminate\Http\Request;


date_default_timezone_set('Asia/Karachi');

class ImageController extends Controller
{
    /**
     * uploading image taking two parameters
     * one is jwt for
     */
    public function uploadImage(Request $req) {
        $arr=(array)$req->file;
       // $img=$arr["\x00Symfony\Component\HttpFoundation\File\UploadedFile\x00originalName"];
        // $arr=explode('.',$img);
        // $image_name=$arr[0];
        // $extension=$arr[1];
        $image_name="hello";
        $extension="png";
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
        // $image=$req->file('file')->store('images');
        // if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        // $url = "https://";
        // else
        //     $url = "http://";
        // $url.= $_SERVER['HTTP_HOST'];
        // $file=$url."/api/storage/".$image;
        $uid=$req->data->_id;
        $date=date("d/m/Y"); //date pattern date/month/year
        $time= date("h:i:sa");
        $conn=new DataBaseConnection();
        $conn->get_connection('images')->insertOne([
            "image"=>$pathD,
            "image_name"=>$image_name,
            "created_at_time" => $time,
            "Created_at_date" => $date,
            "accessor"=>"hidden",
            "extension"=>$extension,
            "user_id"=>$uid,
    ]);
        return response(["message"=>"Image Uploaded Successfuly"]);
    }
    /**
     * deleting and unlinking image
     * taking two parameters
     * one is jwt for user id who is going to delete image
     * and second one is image id
     */

    public function deleteImage(Request $req)
    {
        $conn=new DataBaseConnection();
        $image_id=$req->image_id;
        $imagename=explode("/",$req->data->image);
        $path = storage_path("app/images".'/'.$imagename[4]);
        if (file_exists($path)) {
            unlink($path);
        }
        $image_id2=new \MongoDB\BSON\ObjectId($image_id);
        $conn->get_connection('images')->deleteOne(['_id'=>$image_id2]);
        return response(["message"=>"image deleted"],200);
    }
/**
 * searching images against following filters and userid
 * date, time, name, extensions, private, public, hidden
 */
    public function searchImages(Request $req)
    {
        $conn=new DataBaseConnection();
        $uid=$req->data->_id;
        $filter=[];
        $filter['user_id']=$uid;
        if($req->date!=null){ $filter['Created_at_date']=$req->date;}
        if($req->time!=null){ $filter['created_at_time']=$req->time;}
        if($req->image_name!=null){ $filter['image_name']=$req->image_name;}
        if($req->extension!=null){$filter['extension']= $req->extension;}
        if($req->accessor!=null){ $filter['accessor']=$req->accessor;}
        $data=$conn->get_connection('images')->find($filter);
        $data1=$data->toArray();
        if($data1!=null){
            return response([$data1]);
        }
        else{
            return response(["message"=>"data not found"],404);
        }
    }
    /**
     * listing images against one user
     * getting one parameter
     * i.e jwt token
     */

    public function listImages(Request $req) {
        $conn=new DataBaseConnection();
        $uid=$req->data->_id;
        $data=$conn->get_connection('images')->find(["user_id"=>$uid]);
        $data1=$data->toArray();
        if($data1!=null) {
            return response([$data1]);
        }
        else{
            return response(["message"=>"no data found"],404);
        }
    }
    /**
     * making images public getting two parameters
     * getting wo parameters
    * one is jwt token for user id  who is going to hide image
    * second one image id
     */

    public function makePublic(Request $req) {
        $conn=$req->data->db;
        $uid=$req->data->_id;
        $image_id=new \MongoDB\BSON\ObjectId($req->image_id);
        $conn->get_connection("images")->updateOne(['_id'=>$image_id,'user_id'=>$uid],
        ['$set'=>["accessor"=> "public"]]);
        return response(["message"=>"updated successfuly"],200);
    }
/**
 * making images private and getting three parameters
 * one is jwt token for user id who is making photo private
 * second is email to which you want to see your private photo
 * and third one is image id
 */
    public function makePrivate(Request $req) {
        $conn=$req->data->db;
        $uid=$req->data->_id;
        $image_id=new \MongoDB\BSON\ObjectId($req->image_id);
        $conn->get_connection("images")->updateOne(['_id'=>$image_id,'user_id'=>$uid],
        ['$set'=>["accessor"=> "private","Allowed_Emails" => []]]);
        return response(["message"=>"updated successfuly"],200);
    }
/**
 * making images hidden
 * getting wo parameters
 * one is jwt token for user id who is going to hide image
 * second one image id
 */
    public function makeHidden(Request $req) {
        $conn=$req->data->db;
        $uid=$req->data->_id;
        $image_id=new \MongoDB\BSON\ObjectId($req->image_id);
        $conn->get_connection("images")->updateOne(['_id'=>$image_id,'user_id'=>$uid],
        ['$set'=>["accessor"=> "hidden"]]);
        return response(["message"=>"updated successfuly"],200);
    }

/**
 * add email in to the allowed email array
 * it will one email
 */
    public function addEmail(Request $req){
        $conn=new DataBaseConnection();
        $image_id=new \MongoDB\BSON\ObjectId($req->image_id);
        $email=$req->email;
        $conn->get_connection('images')->updateOne(["_id" => $image_id,"accessor"=> "private"],['$push'=>["Allowed_Emails" => $email]]);
    }

/**
 * remove email from allowed email array
 * it will get one parameter as email
 */
    public function removeOneEmail(Request $req) {
        $conn=new DataBaseConnection();
        $image_id=new \MongoDB\BSON\ObjectId($req->image_id);
        $email=$req->email;
        $conn->get_connection('images')->updateOne(array("user_id"=>$req->data->_id,"_id" => $image_id,"accessor"=>"private"), array('$pull'=>array("Allowed_Emails" => $email)));
    }

/**
 * get image link
 * it is getting two parameters one is jwt
 * and one is image id
 */
    public function getLink(Request $req)
    {
        $conn=new DataBaseConnection();
        $image_id=new \MongoDB\BSON\ObjectId($req->image_id);
        $data=$conn->get_connection('images')->findOne(['_id'=>$image_id]);
        return response(["image link"=>$data->image]);
    }

    public function hitImageLink(Request $request, $filename){
        $headers = ["Cache-Control" => "no-store, no-cache, must-revalidate, max-age=0"];
        $path = storage_path("app/public/images".'/'.$filename);
         if (file_exists($path)) {
            return response()->download($path, null, $headers, null);
        }
        return response()->json(["error"=>"error downloading file"],400);
    }
}
