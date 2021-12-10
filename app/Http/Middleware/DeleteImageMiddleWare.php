<?php

namespace App\Http\Middleware;

use App\Services\DataBaseConnection;
use Closure;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Cast\Object_;

class DeleteImageMiddleWare
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
        $image_id = new \MongoDB\BSON\ObjectId($req->image_id);
        $data =$conn->get_connection('images')->findOne(['_id'=>$image_id,"user_id"=>$req->data->_id]);
        if($data!= null) {
            return $next($req->merge(["data"=>$data]));
            
        }
        else{
            return response(["message"=>"data not found"],404);
        }
    }
}
