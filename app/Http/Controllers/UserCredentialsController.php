<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SignupValidation; // UserSignupValidation Request 
use App\Http\Requests\LoginValidation; // UserLoginValidation Request 
use App\Http\Requests\UserForgotValidation; // UserForgotValidation Request
use App\Http\Requests\UserChangePasswordValidation; // User UserChangePasswordValidation Request
use App\Http\Requests\UserProfileUpdateValidation; // User UserProfileUpdateValidation Request
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

            // simple store profile image path in storage folder
            // $profile = $user->profile = $req->file('profile')->store('profile');

            // creates a local path for image so user can access the image directly.
            $profile_picture=$req->file('profile')->store('images');
            $profile_path=$_SERVER['HTTP_HOST']."/user/storage/".$profile_picture;


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
                'profile'              =>       $profile_path,
                'name'                 =>       $name,
                'age'                  =>       $age,
                'password'             =>       $password,
                'email'                =>       $email,
                'status'               =>       $status,
                'verify_token'         =>       $verify_token,
                'remember_token'       =>       null,
                'email_verified_at'    =>       null,
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
            $password = $user->password = $req->input('password');

    
            if(Hash::check($password, $pas))
            {
                if($status == 0)
                {
                    $jwt_connection = new JWT_Service();
                    $jwt = $jwt_connection->get_jwt();

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
                return response()->json(['Message' => 'Invalid User Credentials..!!!'], 404);
                //return response(['Message' => 'Your email '.$user->email.' is not verified. Please verify your email first.']);
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }
    

    // user update profile details function
    function user_update_profile_details(UserProfileUpdateValidation $req)
    {
        try
        {
            // get all record of user from middleware where token is getting checked.
            $user_record = $req->user_data;
            
            if(!empty($user_record))
            {
                // get token from middleware
                $token = $user_record->remember_token;

                // for logic purpose for user update credentials
                $plus = $minus = 0;

                // get validated data
                $token = $req->input('token');
                if(empty($req->profile))
                {
                    $profile = null;    
                }
                else
                {
                    // simple way to store profile(image) path.
                    //$profile = $req->file('profile')->store('profile');

                    // creates a local path for image so user can access the image directly.
                    $profile_picture=$req->file('profile')->store('images');
                    $profile_path=$_SERVER['HTTP_HOST']."/user/storage/".$profile_picture;
                }
                $name = $req->input('name');
                $age = $req->input('age');
                if(empty($req->password))
                {
                    $password = null;    
                }
                else
                {
                    $password = Hash::make($req->input('password')); // return hashed password
                }
                $email = $req->input('email');


                // make an associative array and put all details in it.
                $data_arr = ['profile' => $profile_path, 'name' => $name, 'age' => $age, 'password' => $password, 'email' => $email];

                // run for each loop and update those enteties who are not null.
                foreach($data_arr as $key=>$value)
                {
                    if(!empty($value))
                    {
                        $plus++;

                        $coll = new MongoDatabaseConnection();
                        $table = 'users';
                        $coll2 = $coll->db_connection();

                        //echo "$key is at $value\n";
                        // dd(['token' => $token, 'picture' => $profile, 'name' => $name, 'age' => $age, 'password' => $password, 'email' => $email]);

                        $coll2->$table->updateMany(array("remember_token"=>$token),
                        array('$set'=>array($key => $value)));
                    }
                    else
                    {
                        $minus++;
                        continue;
                    }
                }

                // to confirm if our logic is working or not (for verification).
                //dd(['plus' => $plus, 'minus' => $minus]);

                if(($plus + $minus) == 5 && $plus != 0) // if plus and minus is == 5 and $plus is != to 0 then go in else that means there was nothing to update
                {
                    return response()->json(['Message' => 'User Credentials Updated'], 200);    
                }
                else if ($minus == 5)
                {
                    return response()->json(['Message' => 'Nothing to Update.'], 200);   
                }
                else
                {
                    return response()->json(['Message' => 'Something went worng while updating user credentials.'], 404);   
                }
            }
            else
            {
                return response()->json(['Message' => 'This user does not exist...!!'], 404);
            } 
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }


    // user forgets password after signup and can't login, so reset password function.
    function userForgetPassword(UserForgotValidation $req)
    {
        try
        {
            $mail = $req->input('email');
    
            $coll = new MongoDatabaseConnection();
            $table = 'users';
            $coll2 = $coll->db_connection();
    
            $find = $coll2->$table->findOne(
            [
                'email' => $mail,
            ]);
            
            if(!empty($find))
            {
                // get data of email verified from user
                $verfiy =$find->email_verified_at;
    
                if(!empty($verfiy))
                {
                    $otp=rand(1000,9999);
                    $coll2->$table->updateMany(array("email"=>$mail),
                    array('$set'=>array('verify_token' => $otp)));
    
                    $send_email_verify = new Email_Service();
                    $result = $send_email_verify->sendMailForgetPassword($mail,$otp);
                    return response()->json(['Message'=> $result], 200);
                }
                else
                {
                    return response()->json(['Message'=>'User not Exists'], 404);
                }
            }
            else
            {
                return response()->json(['Message'=>'User not Exists'], 404);
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }


    // get otp-token and veirfy then update the user new password function.
    function userChangePassword(UserChangePasswordValidation $req)
    {
        try
        {
            $user = new User;
            $mail = $user->email = $req->input('email');
            $otp = $user->otp = $req->input('otp');
            $pass=Hash::make($req->input('password'));
    
            $coll = new MongoDatabaseConnection();
            $table = 'users';
            $coll2 = $coll->db_connection();
    
            $find = $coll2->$table->findOne(
            [
                'email' => $mail,
            ]);
            
            if(!empty($find))
            {
                $token1 = $find['verify_token']; 
    
                if($token1==$otp)
                {
                    $coll2->$table->updateMany(array("email"=>$mail),
                    array('$set'=>array('password' => $pass)));
    
                    return response()->json(['Message'=>'Your Password has been updated so now you can login easily.. Thankyou..!!!!. '], 200);
                }
                else{
                    return response()->json(['Message'=>'Otp Does Not Match. '], 404);
                }
            }
            else{
                return response()->json(['Message'=>'Please Enter Valid Mail. '], 404); 
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }


    // user logout function
    public function user_logout(Request $req)
    {
        try
        {
            // get all record of user from middleware where token is getting checked
            $user_record = $req->user_data;


            if(!empty($user_record))
            {
                // get token id from middleware 
                $token = $user_record->remember_token;

                $coll = new MongoDatabaseConnection();
                $table = 'users';
                $coll2 = $coll->db_connection();
        
                $find = $coll2->$table->findOne(
                [
                    'remember_token' => $token,
                ]);

                if(!empty($find))
                {
                    $coll2->$table->updateMany(array("remember_token"=>$token),
                    array('$set'=>array('remember_token' => null, 'status' => '0')));
    
                    return response()->json(['Message' => 'Logout Succeccfully..!!'],200);
                }
                else
                {
                    return response()->json(['Message' => 'Session is expired..!!'], 404);
                }
            }
            else
            {
                return response()->json(['Message' => 'Token not found or expired..!!'], 404);
            } 
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }
}
