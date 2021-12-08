<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhotosController; // Photos Controller
use GuzzleHttp\Middleware;

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

// token authentication
Route::group(['middleware' => "tokenAuth"], function()
{
    // upload photo 
    Route::post('user_upload_photo', [PhotosController::class, 'upload_photo']);

    // delete photo 
    Route::post('user_delete_photo', [PhotosController::class, 'delete_photo']);

    // search photo
    Route::post('user_search_photo', [PhotosController::class, 'search_photos']);

    // make photo public
    Route::post('make_photo_public', [PhotosController::class, 'make_photo_public']);

    // make photo hidden
    Route::post('make_photo_hidden', [PhotosController::class, 'make_photo_hidden']);

    // make photo private
    Route::post('make_photo_private', [PhotosController::class, 'make_photo_private']);

    // remove specfic access for email for private photo
    Route::post('remove_photo_private_email', [PhotosController::class, 'remove_specfic_email']);

    // get a shareable link
    Route::post('get_a_shareable_link', [PhotosController::class, 'get_a_shareable_link'])->middleware('getAShareableLink');
});

