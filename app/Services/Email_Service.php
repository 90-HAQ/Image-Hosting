<?php
namespace App\Services;
use App\Jobs\SendEmailJob; // queue mail job
use Illuminate\Support\Facades\Mail; // simple way to send email 
use App\Mail\testmail; // call testmail.blade.php


class Email_Service
{
    /***
     * @ Function : Signup Send Mail @
     * 
     * Commentings,
     * line 18 : // get request type and change http tppe according to it.
     * line 29 : // user only send email and at backend email is dispatched automatically. 
     * line 32 : // simple way to send emails.
     */
    public function sendmail($sendto, $verify_token)
    {
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
        {
            $url = "https://";
        }
        else
        {
            $url = "http://";
        }
        $url.= $_SERVER['HTTP_HOST'];
        $details = [ 'title' =>  'Signup Verification.', 'body'  =>  'Please Verify your Account. Please Click on this link to verify ' .$url.'/user/welcome_login'.'/'.$sendto.'/'.$verify_token ];
        dispatch(new SendEmailJob($sendto, $details));
        //$email->handle();
        //Mail::to($sendto)->send(new testmail($details));
        return response()->json(['Message' => 'Email has been sent for Verification, Please verify your Account.']);
    }


    /***
     * @ Function : Send Otp For New Password @
     * 
     * Commentings,
     * line 47 : // user only send email and at backend email is dispatched automatically. 
     * line 49 : // simple way to send emails.
    */
    function sendMailForgetPassword($mail,$otp)
    {
        $details=[ 'title'=> 'Forget Password Verification', 'body'=> 'Your OTP is '. $otp . ' Please verify and update your password.' ]; 
        dispatch(new SendEmailJob($mail, $details));
        //$email->handle();
        //Mail::to($mail)->send(new testmail($details));
        return response()->json(['Message' => 'An OTP has been sent to '.$mail.' , Please verify and proceed further.']);
    }

}