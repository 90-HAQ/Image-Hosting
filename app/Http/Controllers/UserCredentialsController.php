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
use App\Services\Base_64_Conversion; // get base-64 conversion


class UserCredentialsController extends Controller
{
    /***
     * @@ Function : User Signup's A New Account @@
     * 
     * Commenting,
     * line 39 : // get all requested parameters in specfic variables.
     * line 43 : // get password in two seprate variables.
     * line 43 : // send data back to user for updation profile credentials purpose with original password.
     * line 43 : // so here hash password will not make a problem for front-end.
     * line 44 : // get hashed password.
     * line 49 : // get decoded base-64 image from base_64_coonversion_of_image Service.                    
    */ 
    public function signup(SignupValidation $req)
    {
        try
        {
            $user = new User;
            $non_base_image =  $req->image;
            $name = $user->name = $req->name;
            $age = $user->age = $req->age;
            $password = $org_pass = $user->password = $req->password; 
            $password = Hash::make($password);
            $sendto = $email = $user->email = $req->email;
            $status = $user->status = 0;
            $verify_token = $user->verify_token = rand(10, 5000);
            $base = new Base_64_Conversion;
            $image = $base->base_64_coonversion_of_image($non_base_image);
            $profile_path = $image['path'];
            $Adata = ['image' => $profile_path, 'name' => $name, 'age' => $age, 'password' => $org_pass, 'email' => $email];
            $coll = new MongoDatabaseConnection();
            $table = 'users';
            $coll2 = $coll->db_connection();
            $insert = $coll2->$table->insertOne([ 'profile' => $profile_path, 'name' => $name, 'age' => $age, 'password' => $password, 'email' => $email, 'status' => $status, 'verify_token' => $verify_token, 'remember_token' => null, 'email_verified_at' => null ]);
            if(!empty($insert))
            {
                $send_email_verify = new Email_Service();
                $result = $send_email_verify->sendmail($sendto, $verify_token);
                return response(['Message' => $result, 'Data' => $Adata],200);
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


    /***
     * @@ Function : User Verify Account Through Mail@@
     * 
     * Commenting,
     * line 87 : // time and date will be updated in db after verifing your account.
    */ 
    public function welcome_to_login($email, $verify_token)
    {
        try
        {
            $coll = new MongoDatabaseConnection();
            $table = 'users';
            $coll2 = $coll->db_connection();
            $find = $coll2->$table->findOne([ 'email' => $email, 'verify_token' => (int)$verify_token ]);
            if(!empty($find))
            {
                $coll2->$table->updateMany(array("email"=>$email), array('$set'=>array('email_verified_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'))));
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


    /***
     * @@ Function : User Login To Account @@
     * 
     * Commenting,
     * line 116 : // get all record of user from verifiedAccount-middleware where email_verified_at is getting checked.
     * line 117 : // get status and password in order to proceed further.
     * line 119 : // get validated data in new variables from request form data.
    */ 
    public function login(LoginValidation $req)
    {
        try
        {
            $pas = 0;
            $status = 0;
            $user_record = $req->user_data; 
            $pas = $user_record->password;  $status = $user_record->status;
            $user = new User;
            $email = $user->email = $req->email; $password = $user->password = $req->password; 
            if(Hash::check($password, $pas))
            {
                if($status == 0)
                {
                    $jwt_connection = new JWT_Service();
                    $jwt = $jwt_connection->get_jwt();
                    $coll = new MongoDatabaseConnection();
                    $table = 'users';
                    $coll2 = $coll->db_connection();
                    $coll2->$table->updateMany(array("email"=>$email), array('$set'=>array('remember_token' => $jwt, 'status' => '1')));
                    return response()->json(['Message' => 'Now you are logged In', 'token' => $jwt], 200);
                }
                else
                {
                    return response()->json(['Message' => 'You are Already Logged In..!!!'], 400);
                }
            }
            else
            {
                return response()->json(['Message' => 'Invalid User Credentials..!!!'], 404);
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }


    /***
     * @@ Function : User Updates Profile @@
     * 
     * Commenting,
     * line 168 : // get all record of user from middleware where token is getting checked.
     * line 171 : // for logic purpose for user update credentials
     * line 172 : // get token from middleware
     * line 173 : // get validated data from request form data.
     * line 183 : // base service image conversion service
     * line 184 : // get function data in return 
     * line 193 : // return hashed password
     * line 195 : // make an associative array and put all details in it.
     * line 196 : // run for each loop and update those enteties who are not null.
     * line 212 : // if plus and minus is == 5 and $plus is != to 0 then go in else that means there was nothing to update
    */    
    function user_update_profile_details(UserProfileUpdateValidation $req)
    {
        try
        {
            $user_record = $req->user_data;
            if(!empty($user_record))
            {
                $plus = $minus = 0; 
                $token = $user_record->remember_token; 
                $non_base_image = $req->profile; 
                $name = $req->name;
                $age = $req->age;
                $email = $req->email;
                if(empty($non_base_image))
                {
                    $profile_path = null;    
                }
                else
                {
                    $base = new Base_64_Conversion; 
                    $image = $base->base_64_coonversion_of_image($non_base_image); 
                    $profile_path = $image['path'];
                }
                if(empty($req->password))
                {
                    $password = null;    
                }
                else
                {
                    $password = Hash::make($req->password); 
                }
                $data_arr = ['profile' => $profile_path, 'name' => $name, 'age' => $age, 'password' => $password, 'email' => $email];
                foreach($data_arr as $key=>$value)
                {
                    if(!empty($value))
                    {
                        $plus++;
                        $coll = new MongoDatabaseConnection();
                        $table = 'users';
                        $coll2 = $coll->db_connection();
                        $coll2->$table->updateMany(array("remember_token"=>$token), array('$set'=>array($key => $value)));
                    }
                    else
                    {
                        $minus++; 
                        continue;
                    }
                }
                if(($plus + $minus) == 5 && $plus != 0) 
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


    /***
     * @@ Function : User Forgot Password @@
     * 
     * Commenting,
     * line 256 : // get data of email verified from user
     * line 261 : // call email service to send an email to the user.
     * line 262 : // send a new generated otp to reset password.
    */     
    function userForgetPassword(UserForgotValidation $req)
    {
        try
        {
            $mail = $req->email;
            $coll = new MongoDatabaseConnection();
            $table = 'users';
            $coll2 = $coll->db_connection();
            $find = $coll2->$table->findOne([ 'email' => $mail ]);
            if(!empty($find))
            {
                $verfiy =$find->email_verified_at; 
                if(!empty($verfiy))
                {
                    $otp=rand(1000,9999);
                    $coll2->$table->updateMany(array("email"=>$mail), array('$set'=>array('verify_token' => $otp)));
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


    /***
     * @@ Function : User Change Password @@
     * 
     * Commenting,
     * line 294 : // get validated request from form data.
     * line 303 : // get verify token form db to match otp(param).
    */     
    function userChangePassword(UserChangePasswordValidation $req)
    {
        try
        {
            $user = new User;
            $mail = $user->email = $req->email; 
            $otp = $user->otp = $req->otp; 
            $pass = Hash::make($req->password);
            $coll = new MongoDatabaseConnection();
            $table = 'users';
            $coll2 = $coll->db_connection();
            $find = $coll2->$table->findOne( [ 'email' => $mail, ]);
            if(!empty($find))
            {
                $token1 = $find['verify_token']; 
                if($token1==$otp)
                {
                    $coll2->$table->updateMany(array("email"=>$mail), array('$set'=>array('password' => $pass)));
                    return response()->json(['Message'=>'Your Password has been updated so now you can login easily.. Thankyou..!!!!. '], 200);
                }
                else
                {
                    return response()->json(['Message'=>'Otp Does Not Match. '], 404);
                }
            }
            else
            {
                return response()->json(['Message'=>'Please Enter Valid Mail. '], 404); 
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }


    /***
     * @@ Function : User Logout @@
     * 
     * Commenting,
     * line 337 : // get all record of user from middleware where token is getting checked.
     * line 340 : // get token id from middleware.
    */  
    public function user_logout(Request $req)
    {
        try
        {
            $user_record = $req->user_data; 
            if(!empty($user_record))
            {
                $token = $user_record->remember_token;     
                $coll = new MongoDatabaseConnection();
                $table = 'users';
                $coll2 = $coll->db_connection();
                $find = $coll2->$table->findOne([ 'remember_token' => $token ]);
                if(!empty($find))
                {
                    $coll2->$table->updateMany(array("remember_token"=>$token), array('$set'=>array('remember_token' => null, 'status' => '0')));
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
