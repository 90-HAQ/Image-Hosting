<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SignupValidation; // UserSignupValidation Request 
use App\Http\Requests\LoginValidation; // UserLoginValidation Request 
use App\Models\User; // User Model
use Illuminate\Support\Facades\Hash; // for hashing the password
use App\Services\MongoDatabaseConnection; // connection with Mongo Database
use App\Services\Email_Service; // call email services to send email
use App\Services\JWT_Service; // generate jwt token


class UserCredentialsController extends Controller
{
    // user signup function
    public function signup(SignupValidation $req)
    {
        try
        {
            $user = new User;

            $profile = $user->profile = $req->file('profile')->store('profile');
            $name = $user->name = $req->input('name');
            $age = $user->age = $req->input('age');
            $password = $user->password = Hash::make($req->input('password')); // return hashed password
            $sendto = $email = $user->email = $req->input('email');
            $status = $user->status = 0;
            $verify_token = $user->verify_token = rand(10, 5000);


            $coll = new MongoDatabaseConnection();
            $table = 'users';
            $coll2 = $coll->db_connection();

            $insert = $coll2->$table->insertOne(
            [
                'profile'              =>       $profile,
                'name'                 =>       $name,
                'age'                  =>       $age,
                'password'             =>       $password,
                'email'                =>       $email,
                'status'               =>       $status,
                'verify_token'         =>       $verify_token,
                'remember_token'       =>       'null',
                'email_verified_at'    =>       'null',
            ]);

            if(!empty($insert))
            {
                $send_email_verify = new Email_Service();
                $result = $send_email_verify->sendmail($sendto, $verify_token);
                return response($result,200);
            }
            else
            {
                return response(['Message'=>'Something went wrong in Signup Api..!!!'], 400);
            }  


        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }


    // welcome api for user email verification function
    public function welcome_to_login($email, $verify_token)
    {
        try
        {
            $coll = new MongoDatabaseConnection();
            $table = 'users';
            $coll2 = $coll->db_connection();
    
            $find = $coll2->$table->findOne(
            [
                'email' => $email,
                'verify_token' => (int)$verify_token,
            ]);
    
            if(!empty($find))
            {
                $coll2->$table->updateMany(array("email"=>$email),
                array('$set'=>array('email_verified_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'))));
                
                return response(['Message'=>'Your Email has been Verified'], 200);
            }
            else
            {
                return response(['Message' => 'Something went wrong in Welcome To Login Api..!!!'], 400);
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }


    // user login function
    public function login(LoginValidation $req)
    {
        try
        {
            $pas = 0;
            $status = 0;

            // get all record of user from verifiedAccount-middleware where email_verified_at is getting checked.
            $user_record = $req->user_data;
            $pas = $user_record->password; 
            $status = $user_record->status;  

    
            $user = new User;
            $email = $user->email = $req->input('email');
            $user->password = $req->input('password');
    
            if(Hash::check($user->password, $pas))
            {
                if($status == 0)
                {
                    $jwt_connection = new JWT_Service();
    
                    $jwt = $jwt_connection->get_jwt();
                    // check if jwt is generating or not.
                    //echo $jwt;

                    $coll = new MongoDatabaseConnection();
                    $table = 'users';
                    $coll2 = $coll->db_connection();
    
                    $coll2->$table->updateMany(array("email"=>$email),
                    array('$set'=>array('remember_token' => $jwt, 'status' => '1')));
    
                    return response()->json(['Message' => 'Now you are logged In', 'access_token' => $jwt], 200);
                }
                else
                {
                    return response()->json(['Message' => 'You are Already Logged In..!!!'], 400);
                }
            }
            else
            {
                return response()->json(['Message' => 'Your email '.$user->email.' does not exists in our record.'], 404);
                //return response(['Message' => 'Your email '.$user->email.' is not verified. Please verify your email first.']);
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }
    
    
}