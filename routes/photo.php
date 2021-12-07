<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhotosController; // Photos Controller
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

// upload photo 
Route::post('user_upload_photo', [PhotosController::class, 'upload_photo'])->middleware('tokenAuth');

// delete photo 
Route::post('user_delete_photo', [PhotosController::class, 'delete_photo'])->middleware('tokenAuth');

// search photo
Route::post('user_search_photo', [PhotosController::class, 'search_photos'])->middleware('tokenAuth');
