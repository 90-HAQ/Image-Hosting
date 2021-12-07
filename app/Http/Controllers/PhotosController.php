<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MongoDatabaseConnection; // Make connection with MongoDB
use App\Http\Requests\PhotoUploadValidation; // PhotoUploadValidation Request
use App\Http\Requests\PhotoDeleteValidation; // PhotoDeleteValidation Request
use App\Http\Requests\PhotoSearchingValidation; // PhotoSearchingValidation Request 


class PhotosController extends Controller
{
    // user upload photo
    public function upload_photo(PhotoUploadValidation $req)
    {
        try
        {
            // get all record of user from middleware where token is getting checked.
            $user_record = $req->user_data;


            if(!empty($user_record))
            {
                // get token from middleware
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
                    // get user id
                    $id = $find['_id']; 


                    // get validated data
                    // creates a local path for image so user can access the image directly.
                    $access =  $req->access;
                    
                    $photo=$req->file('photo');
                    
                    $photo_array = (array)$photo; // put photo in array
                    
                    $name = $photo_array["\x00Symfony\Component\HttpFoundation\File\UploadedFile\x00originalName"]; // get photo name

                    $photoData = explode('.',$name); // seprated the data where . is found

                    $name = $photoData[0]; // get photo name
                    $ext = $photoData[1]; // get photo extension

                    
                    $photo=$req->file('photo')->store('images'); // store image path
                    $photo_path=$_SERVER['HTTP_HOST']."/user/storage/".$photo; // get localhost image path
    
                    
                    $coll = new MongoDatabaseConnection();
                    $table = 'photos';
                    $coll2 = $coll->db_connection();
        
                    $insert = $coll2->$table->insertOne(
                    [
                        'user_id'           =>       $id,
                        'photo_name'        =>       $name,
                        'Photo_extension'   =>       $ext,
                        'photo_path'        =>       $photo_path,
                        'date'              =>       date("d:m:Y"), // date , month ,year
                        'time'              =>       date("h:i:sa"), // hours , minutes ,seconds
                        'access'            =>       $access,
                    ]);

                    if(!empty($insert))
                    {
                        return response(['Message'=>'Photo Uploaded'], 200);                        
                    }
                    else
                    {
                        return response(['Message'=>'Something went while Uploading Photo..!!!'], 400);                        
                    }
                }
            }
            else
            {
                return response(['Message'=>'Something went while Uploading Photo..!!!'], 400);                        
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }


    // user delete photo
    public function delete_photo(PhotoDeleteValidation $req)
    {
        try
        {
            // get all record of user from middleware where token is getting checked
            $user_record = $req->user_data;

            if(!empty($user_record))
            {
                // get user id from middleware 
                $uid = $user_record->_id;

                // get pid from user request
                $pid = $req->input('photoID');

                $coll = new MongoDatabaseConnection();
                $table = 'photos';
                $coll2 = $coll->db_connection();
    
                // this error will be always shown so ignore it.
                $ppid = new \MongoDB\BSON\ObjectId($pid);
    
                $delete = $coll2->$table->deleteOne(array("user_id"=> $uid, "_id"=>$ppid));
                
                if(!empty($delete))
                {
                    return response(['Message'=>'Photo Deleted']);   
                }
                else
                {
                    return response(['Message' => 'Something went wrong while deleting photo.'], 400);                                 
                }                               
            }
            else
            {
                return response()->json(['Message'=>'Post Id does not exist.'], 404);
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }

    public function search_photos(PhotoSearchingValidation $req)
    {
        try
        {
            // get all record of user from middleware where token is getting checked
            $user_record = $req->user_data;

            if(!empty($user_record))
            {
                // get user id from middleware 
                $uid = $user_record->_id;
                // this error will be always shown so ignore it.
                $uuid = new \MongoDB\BSON\ObjectId($uid);

                $date = $req->input('date');
                $time = $req->input('time');
                $name = $req->input('name');
                $extension = $req->input('extension');
                $accessors = $req->input('accessors');

                //dd($date, $time, $name, $extension, $accessors);

                $put_data = [];

                if($uid)            { $put_data['user_id'] = $uid; } // check name is null or not.
                if($name)           { $put_data['photo_name'] = $name; } // check name is null or not.
                if($extension)      { $put_data['Photo_extension'] = $extension; } // check extension is null or not.
                if($date)           { $put_data['date'] = $date; } // check date is null or not.
                if($time)           { $put_data['time'] = $time; } // check time is null or not.
                if($accessors)      { $put_data['access'] = $accessors; } // check accessors is null or not.
                

                $coll = new MongoDatabaseConnection();
                $table = 'photos';
                $coll2 = $coll->db_connection();
        
                $data = $coll2->$table->find($put_data);

                // converts objects into json and returns 
                //$objects = json_decode(json_encode($data->toArray(),true));
                $objects = $data->toArray();

                if(!empty($objects))
                {
                    return response(['Message'=> $objects]);
                }
                else
                {
                    return response(['Message'=> 'Searched Data not found.']);
                }   
            }
            else
            {
                return response()->json(['Message'=>'User does not exist.'], 404);
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }

    }
}
