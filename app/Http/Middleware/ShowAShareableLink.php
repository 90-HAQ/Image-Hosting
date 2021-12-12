<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MongoDatabaseConnection; // Database connection

class ShowAShareableLink
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
         * check if image is public then allow to anyone
         * 
         * check if image is hidden  is not allowed
         * 
         * check if image is private then chack given access
         * 
        */ 
        
        //dd($method = $req->method()); to check what the method type is

        $token = $req->token;
        $link = $req->link;
        
        $coll = new MongoDatabaseConnection(); // get database file
        $coll2 = $coll->db_connection(); // make db connection

        // another way to get alll record with conditions of 
        //(photoid and check email (then wmail will be required from user so it is not effective))
        /* $find1 = $coll2->$table->findOne(["_id"=> $photoID, "photo_access_to" => $check_email]); */

        $table = 'photos'; // table name
        $find1 = $coll2->$table->findOne([ 'photo_path' => $link ]);
        
        $table = 'users'; // table name
        $find2 = $coll2->$table->findOne([ 'remember_token' => $token ]);

        // check if photo exists by (photo_record)
        if(!empty($find1))
        {

            $check_access = $find1['access']; // get image access type (Public / Hidden / Private)
            
            $check_mail = $find2['email']; // get email to check if user is allowed to see image or not.

            $permission = 0; // allowed(1) or not-allowed(0) (by default) is not-allowed
            
            if($check_access == "public") // check is access is public
           {
               $permission = 1;
               $new_link = ['access' => $check_access, 'link' => $link, 'permission' => $permission];
                return $next($req->merge(['image_link' => $new_link]));   
           }
           else if($check_access == "hidden") // check is access is hidden
           {
                // first check user must be login to see his own shared link
                $new_link = ['access' => $check_access, 'link' => $link, 'permission' => $permission];
                return $next($req->merge(['image_link' => $new_link]));   
           }
           else if($check_access == "private") // check is access is private
           {    
                $email_data = $find1['photo_access_to']; // get data of private photo access to email in a variable
                $email_match = false;            
                
                foreach($email_data as $key)
                {
                    if($key == $check_mail)
                    {
                        $email_match = true;
                    }
                }   //dd($email_match); // value is matching ok... done

                if($email_match == true)
                {
                    $permission = 1;
                    $new_link = ['access' => $check_access, 'link' => $link, 'permission' => $permission];
                    return $next($req->merge(['image_link' => $new_link]));   
                }
                else
                {
                    $new_link = ['access' => $check_access, 'link' => $link, 'permission' => $permission];
                    return $next($req->merge(['image_link' => $new_link]));   
                }
           }   
        }
        else
        {
            return response()->json(['Message' => 'Photo ID does not exists.'], 403);   
        }
    }
}
