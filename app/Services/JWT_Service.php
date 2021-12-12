<?php
namespace App\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Config;

class JWT_Service
{
    /***
     * @ Function : Genereating JWT TOken @
     * 
     * Commentings,
     * line 24 : // jwt token generated.
     * line 25 : // jwt token returned.
     */
    public function get_jwt()
    {
        $key = Config::get('Constant.Key');
        $payload = array(
            "iss" => "localhost",
            "aud" => "users",
            "iat" => time(),
            "nbf" => 1357000000
        );
        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    } 
}