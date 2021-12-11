<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MongoDatabaseConnection; // Make connection with MongoDB
use App\Http\Requests\PhotoUploadValidation; // PhotoUploadValidation Request
use App\Http\Requests\PhotoDeleteValidation; // PhotoDeleteValidation Request
use App\Http\Requests\PhotoSearchingValidation; // PhotoSearchingValidation Request 
use App\Http\Requests\PhotoMakePublic; // PhotoMakePublic Request
use App\Http\Requests\PhotoMakePrivate; // PhotoMakePrivate Request
use App\Http\Requests\RemovePrivateSpecficEmail; // RemovePrivateSpecficEmail Request
use App\Http\Requests\GetAShareableLink; // GetAShareableLink Request
use App\Http\Requests\ShowShareableLinkValidation; // ShowShareableLinkValidation Request



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
                    $access = 'hidden';

                    $name = $req->image_name;
                    $base64_string =  $req->image; // get file in encoded form from the user(front-end)
                    $extension = explode('/', explode(':', substr($base64_string, 0, strpos($base64_string, ';')))[1])[1]; // .jpg .png .pdf
                    $replace = substr($base64_string, 0, strpos($base64_string, ',')+1);
                    $image = str_replace($replace, '', $base64_string); // will get the image name but not original name because the original name is changed. 
                    $image = str_replace(' ', '+', $image); // get image without spaces
                    $fileName = time().'.'.$extension; // get file extension
        
                    // get request type and change http tppe according to it.
                    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
                    {
                        $url = "https://";
                    }
                    else
                    {
                        $url = "http://";
                    }
        
                    $url.= $_SERVER['HTTP_HOST'];
                    $photo_path=$url."/user/storage/images/".$fileName; // set file path in database
                    $path=storage_path('app\\images').'\\'.$fileName; // set file path in project
                    file_put_contents($path,base64_decode($image)); // put path in project storage
    
                    
                    $coll = new MongoDatabaseConnection();
                    $table = 'photos';
                    $coll2 = $coll->db_connection();
        
                    $insert = $coll2->$table->insertOne(
                    [
                        'user_id'           =>       $id,
                        'photo_name'        =>       $name,
                        'Photo_extension'   =>       $extension,
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
                    return response(['Message'=>'Photo Deleted'], 200);   
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


    // user search photo / image
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

                if($uid)            { $put_data['user_id'] = $uuid; } // check name is null or not.
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


    // remove private access to make image public / hidden
    public function remove_private_access($photoID, $uid)
    {
        $coll = new MongoDatabaseConnection();
        $table = 'photos';
        $coll2 = $coll->db_connection();

        $find = $coll2->$table->findOne(
        [
            '_id' => $photoID
        ]);

        $access = $find['access'];

        if($access == "private")
        {
            $coll2->$table->updateOne(array("_id" => $photoID, "user_id" => $uid),
            array('$unset'=>array('photo_access_to' => '')));

            return response()->json(['Message'=>'photo private access email removed'], 200);   
        }
    }     


    // make image / photo public
    public function make_photo_public(PhotoMakePublic $req)
    {
        try
        {
            // get all record of user from middleware where token is getting checked
            $user_record = $req->user_data;

            $uid = $user_record->_id; // get user id from middleware 
            
            $uid = new \MongoDB\BSON\ObjectId($uid); // this error will be always shown so ignore it.

            $photoID = $req->input('photoID');
            
            $photoID = new \MongoDB\BSON\ObjectId($photoID); // this error will be always shown so ignore it.
            
            $access = 'public'; // make user photo public

            $this->remove_private_access($photoID, $uid); // if image is alread private then it will be updated

            $coll = new MongoDatabaseConnection();
            $table = 'photos';
            $coll2 = $coll->db_connection();

            $update = $coll2->$table->updateOne(array("_id" => $photoID, "user_id" => $uid),
            array('$set'=>array('access' => $access)));

            if(!empty($update))
            {
                return response(['Message'=>'Photo Updated to Public'], 200);   
            }
            else
            {
                return response()->json(['Message'=>'You are not allowed to update someone else image.'], 404);
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }

    // make image / photo hidden
    public function make_photo_hidden(PhotoMakePublic $req)
    {
        try
        {
            // get all record of user from middleware where token is getting checked
            $user_record = $req->user_data;

            $uid = $user_record->_id; // get user id from middleware 
            
            $uid = new \MongoDB\BSON\ObjectId($uid); // this error will be always shown so ignore it.

            $photoID = $req->input('photoID');
            
            $photoID = new \MongoDB\BSON\ObjectId($photoID); // this error will be always shown so ignore it.
            
            $access = 'hidden'; // make user photo public

            $this->remove_private_access($photoID, $uid); // if image is alread private then it will be updated

            $coll = new MongoDatabaseConnection();
            $table = 'photos';
            $coll2 = $coll->db_connection();

            $update = $coll2->$table->updateOne(array("_id" => $photoID, "user_id" => $uid),
            array('$set'=>array('access' => $access)));

            if(!empty($update))
            {
                return response(['Message'=>'Photo Updated to Hidden'], 200);   
            }
            else
            {
                return response()->json(['Message'=>'You are not allowed to update someone else image.'], 404);
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }


    // make image / photo private
    public function make_photo_private(PhotoMakePrivate $req)
    {
        try
        {
            // get all record of user from middleware where token is getting checked
            $user_record = $req->user_data;

            $uid = $user_record->_id; // get user id from middleware 
            $uid = new \MongoDB\BSON\ObjectId($uid); // this error will be always shown so ignore it.

            $photoID = $req->input('photoID');
            $photoID = new \MongoDB\BSON\ObjectId($photoID); // this error will be always shown so ignore it.

            $access = 'private'; // make user photo public
            
            $assess_emails = $req->input('assess_emails'); // get emails for private photo in a variable
            $names_arr = explode(',', $assess_emails); // seprate those emails in array

        
            $coll = new MongoDatabaseConnection();
            $table = 'photos';
            $coll2 = $coll->db_connection();

            $update1 = $coll2->$table->updateOne(array("_id" => $photoID, "user_id" => $uid),
            array('$set'=>array('access' => $access)));

            // $update2 = $coll2->$table->updateOne(array("_id" => $photoID, "user_id" => $uid),
            // array('$set'=>array('access_to' => $key)));

            foreach($names_arr as $key)
            {
                $update2 = $coll2->$table->updateOne(["_id"=>$photoID, "user_id"=>$uid],['$push'=>["photo_access_to" => $key]]);
            }

            if(!empty($update1) && !empty($update2))
            {
                return response()->json(['Message'=>'Photo Updated to Private'], 200);   
            }
            else
            {
                return response()->json(['Message'=>'You are not allowed to update someone else image.'], 404);
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }    


    // remove the email for private access
    public function remove_specfic_email(RemovePrivateSpecficEmail $req)
    {
        try
        {
            // get all record of user from middleware where token is getting checked
            $user_record = $req->user_data;

            $uid = $user_record->_id; // get user id from middleware 
            $uid = new \MongoDB\BSON\ObjectId($uid); // this error will be always shown so ignore it.

            $photoID = $req->input('photoID');
            $photoID = new \MongoDB\BSON\ObjectId($photoID); // this error will be always shown so ignore it.

            $email = $req->input('email');

            $coll = new MongoDatabaseConnection();
            $table = 'photos';
            $coll2 = $coll->db_connection();

            $find = $coll2->$table->findOne(
            [
                '_id' => $photoID
            ]);

            $access = $find['access'];

            if($access == "private")
            {
                $coll2->$table->updateOne(array("_id" => $photoID, "user_id" => $uid, "access" => $access),
                array('$pull'=>array('photo_access_to' => $email)));
    
                return response()->json(['Message'=>'photo private access email removed'], 200);   
            }
            else
            {
                return response()->json(['Message'=>'photo is not private'], 200);   
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }


    // generate image link
    public function get_a_shareable_link(GetAShareableLink $req)
    {
        try
        {
            $photoID = $req->input('photoID');
            $photoID = new \MongoDB\BSON\ObjectId($photoID); // this error will be always shown so ignore it.

            $coll = new MongoDatabaseConnection();
            $table = 'photos';
            $coll2 = $coll->db_connection();

            $find = $coll2->$table->findOne(
            [
                '_id' => $photoID
            ]);

            if(!empty($find))
            {
                $link = $find['photo_path'];
                return response()->json(['Image Link :' => $link], 200); 
            }
            else
            {
                return response()->json(['Message' => 'Link not generated.'], 400); 
            }
        }   
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }

    // show image link
    public function show_link(ShowShareableLinkValidation $req)
    {
        try 
        {
            $data = $req->image_link;

            $access = $data['access'];
            $link = $data['link'];

            $link = explode('/',$req->link);
            $new_link = $link[4];            
            $headers = ["Cache-Control" => "no-store, no-cache, must-revalidate, max-age=0"];
            $path = storage_path("app/images".'/'.$new_link);


            if($access == 'public' || $access == 'Public' || $access == 'PUBLIC') // display public image
            {
                if(file_exists($path)) 
                {
                    return response()->download($path, null, $headers, null);
                }
                else
                {
                    return response()->json(["error"=>"error in fetching profile picture"],400);
                }
            }
            else if($access == 'hidden' || $access == 'Hidden' || $access == 'HIDDEN')  // display hidden image
            {
                $msg = "Not Allowed.";
                return response()->json(['Message' => $msg]);
            }
            else if($access == 'private' || $access == 'Private' || $access == 'PRIVATE') // display private image
            {
                if(file_exists($path)) 
                {
                    return response()->download($path, null, $headers, null);
                }
                else
                {
                    return response()->json(["error"=>"error in fetching profile picture"],400);
                }
            }            
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }
}
