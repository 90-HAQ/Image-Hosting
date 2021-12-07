<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MongoDatabaseConnection;

class customAuth
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
        $token = $req->token;

        if(!empty($token))
        {
            $coll = new MongoDatabaseConnection();
            $coll2 = $coll->db_connection();
            $table = 'users';
    
            $find = $coll2->$table->findOne(
            [
                'remember_token' => $token,
            ]);

            if(!empty($find))
            {
                return $next($req->merge(['user_data' => $find]));
            }
            else
            {
                return response()->json(['Message' => 'Your are not Authenticated User.'], 401);
            }
        }
        else
        {
            return response()->json(['Message' => 'Your Token is Empty.'], 404);
        }
    }
}
