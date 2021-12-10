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

header("Access-Control-Allow-Origin: *");

header('Access-Control-Allow-Credentials: true');

header('Access-Control-Allow-Headers: *');


// for image route // Route that create a localpath for images.
Route::any('/storage/images/{filename}',function(Request $request, $filename){

    $headers = ["Cache-Control" => "no-store, no-cache, must-revalidate, max-age=0"];

    $path = storage_path("app/images".'/'.$filename);

    if (file_exists($path)) 
    {
        return response()->download($path, null, $headers, null);
    }
    return response()->json(["error"=>"error in fetching profile picture"],400);
});


// token authentication
Route::group(['middleware' => "tokenAuth"], function()
{
    // upload photo 
    Route::post('/user_upload_photo', [PhotosController::class, 'upload_photo']);

    // delete photo 
    Route::post('/user_delete_photo', [PhotosController::class, 'delete_photo']);

    // search photo
    Route::post('/user_search_photo', [PhotosController::class, 'search_photos']);

    // make photo public
    Route::post('/make_photo_public', [PhotosController::class, 'make_photo_public']);

    // make photo hidden
    Route::post('/make_photo_hidden', [PhotosController::class, 'make_photo_hidden']);

    // make photo private
    Route::post('/make_photo_private', [PhotosController::class, 'make_photo_private']);

    // remove specfic access for email for private photo
    Route::post('/remove_photo_private_email', [PhotosController::class, 'remove_specfic_email']);

    // get a shareable link
    Route::post('/get_a_shareable_link', [PhotosController::class, 'get_a_shareable_link']);

    // show a shareable link
    Route::post('/show_link', [PhotosController::class, 'show_link'])->middleware('getAShareableLink');
});

