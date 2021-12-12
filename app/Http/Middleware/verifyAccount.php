<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MongoDatabaseConnection;

class verifyAccount
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
        
        $email = $req->email;

        $coll = new MongoDatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();

        $find = $coll2->$table->findOne([ 'email' => $email ]);

        if(!empty($find))
        {
            $email_verified = $find['email_verified_at']; 

            if(!empty($email_verified))
            {
                return $next($req->merge(['user_data' => $find]));   
            }
            else
            { 
                return response()->json(['Message' => 'Email is not verified. Please verify your email first'], 403);   
            }
        }
        else
        {
            return response()->json(['Message' => 'Record does not exists in our database.'], 403);   
        }
    }
}
