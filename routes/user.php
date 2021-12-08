<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserCredentialsController; // call UserController Class;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

// Route that create a localpath for images.
Route::any('/storage/images/{filename}',function(Request $request, $filename){

    $headers = ["Cache-Control" => "no-store, no-cache, must-revalidate, max-age=0"];

    $path = storage_path("app/images".'/'.$filename);

    if (file_exists($path)) 
    {
        return response()->download($path, null, $headers, null);
    }
    return response()->json(["error"=>"error in fetching profile picture"],400);
});


// Signup Route for User
Route::post('/signup', [UserCredentialsController::class, 'signup'])->middleware('existAccount');

// user email verification
Route::get('/welcome_login/{email}/{verify_token}', [UserCredentialsController::class, 'welcome_to_login']);

// user login
Route::post('/login', [UserCredentialsController::class, 'login'])->middleware('verifyAccount');

// user forget password
Route::post('/forget_password', [UserCredentialsController::class, 'userForgetPassword'])->middleware('accountExistOrNot');

// user change password
Route::post('/change_password', [UserCredentialsController::class, 'userChangePassword']);


// token authentication
Route::group(['middleware' => "tokenAuth"], function()
{
    // User profile update 
    Route::post('/user_update_profile', [UserCredentialsController::class, 'user_update_profile_details']);

    // user logout
    Route::post('/logout', [UserCredentialsController::class, 'user_logout']);
});