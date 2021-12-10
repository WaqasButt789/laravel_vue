<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class JwtService{

    public function getJwt(){

        $key = "waqas-123";
        $payload = array(
            "iss" => "localhost",
            "aud" => "users",
            "iat" => time(),
            "nbf" => 1357000000
        );

    $jwt = JWT::encode($payload, $key, 'HS256');
    return $jwt;
    // $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    // print_r($decoded);

    }
}


?>
