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


// Signup Route for User
Route::post('/signup', [UserCredentialsController::class, 'signup'])->middleware('existAccount');;

// user email verification
Route::get('/welcome_login/{email}/{verify_token}', [UserCredentialsController::class, 'welcome_to_login']);

// user login
Route::post('/login', [UserCredentialsController::class, 'login'])->middleware('verifyAccount');