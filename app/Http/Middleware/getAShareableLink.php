<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MongoDatabaseConnection; // Database connection

class getAShareableLink
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

        /***
         * 
         * check if image is hidden 
         * 
         * check if image is public
         * 
         * check if image is private 
         * 
         */



        $token = $req->token;
        $photoID = $req->photoID;
        $photoID = new \MongoDB\BSON\ObjectId($photoID);

        $coll = new MongoDatabaseConnection();
        $table = 'photos';
        $coll2 = $coll->db_connection();

        $find1 = $coll2->$table->findOne(
        [
            '_id' => $photoID
        ]);


        // check if photo exists by (photo_id)
        if(!empty($find1))
        {

            // $coll = new MongoDatabaseConnection();
            $table = 'users';
            // $coll2 = $coll->db_connection();
            $find2 = $coll2->$table->findOne(
            [
                'remember_token' => $token
            ]);

    
            $check_mail = $find2['email']; // get email to check if user is allowed to see image or not.

            // get data of photo_id in a data
            $email_data = $find1['photo_access_to'];

            $email_match = false;            
            
            foreach($email_data as $key)
            {
                if($key == $check_mail)
                {
                    $email_match = true;
                }
            }

            dd($email_match); // value is matching ok... done

            /*

            $table = 'photos';
            // $coll2 = $coll->db_connection();
            $find2 = $coll2->$table->findOne(
            [
                'remember_token' => $token
            ]);

    
            /*if(!empty($find))
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
            }*/
            
        }
        else
        {
            return response()->json(['Message' => 'Photo ID does not exists.'], 403);   
        }


    }
}
