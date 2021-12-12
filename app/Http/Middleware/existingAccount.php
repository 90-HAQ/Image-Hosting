<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MongoDatabaseConnection; // connection with Mongo Database

class existingAccount
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

        $insert = $coll2->$table->findOne([ 'email' => $email ]);
        
        if(!empty($insert))
        {
            return response()->json(['Message' => 'Account already exists.'], 302);   
        }
        else
        { 
            return $next($req);
        }
    }
}
