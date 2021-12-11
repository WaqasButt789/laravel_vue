<?php

namespace App\Helpers;

class Base64DecoderHelper
{
    public static function decodeBase64($file)
    {

        $base64_string =  $file;
        $extension = explode('/', explode(':', substr($base64_string, 0, strpos($base64_string, ';')))[1])[1]; // .jpg .png .pdf
        $replace = substr($base64_string, 0, strpos($base64_string, ',')+1);
        $image = str_replace($replace, '', $base64_string);
        $image = str_replace(' ', '+', $image);
        $fileName = time().'.'.$extension;
        $file=[];
        $file[0]=$fileName;
        $file[1]=$image;
        return $file;
    }
}
