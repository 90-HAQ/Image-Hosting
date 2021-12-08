<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MongoDatabaseConnection; //Database connection with MongoDB

class accountExistOrNot
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

        $insert = $coll2->$table->findOne(
        [
            'email' => $email,
        ]);
        
        if(!empty($insert))
        {
            return $next($req);            
        }
        else
        { 
            return response()->json(['Message' => 'Account does not exists. / Wrong Credentials (ADE)'], 302);   
        }
    }
}
