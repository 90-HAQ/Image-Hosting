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
use App\Services\Base_64_Conversion; // get base-64 conversion

class PhotosController extends Controller
{
    /***
     * @@ Function : User Uploads A Photo @@
     * 
     * Commenting,
     * line 36 : // get all record of user from middleware where token is getting checked.
     * line 39 : // get token from middleware in token variable
     * line 46 : // get user id 
     * line 47 : // get validated data                    
     * line 48 : // base-64 image conversion decodation (Service)
     * line 49 : // get base-64-image function data in return 
     * line 53 : // (Date in specfic FORMAT => ("d:m:Y")) => date , month ,year 
     * line 53 : // (Time in specfic FORMAT => ("h:i:sa")) =>  hours , minutes ,seconds 
     */ 
    public function upload_photo(PhotoUploadValidation $req)
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
                $find = $coll2->$table->findOne([ 'remember_token' => $token, ]);
                if(!empty($find))
                {
                    $id = $find['_id'];
                    $name = $req->name;  $non_base_image = $req->photo;  $access = 'hidden';
                    $base = new Base_64_Conversion; 
                    $image = $base->base_64_coonversion_of_image($non_base_image); 
                    $profile_path = $image['path'];
                    $extension = $image['extension'];
                    $table = 'photos';
                    $insert = $coll2->$table->insertOne(['user_id' => $id, 'Photo_extension' => $extension, 'photo_path' => $profile_path, 'date' => date("d:m:Y"), 'time' => date("h:i:sa"), 'access' => $access, 'photo_name' => $name ]);
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


    /***
     * @@ Function : User Deletes A Photo @@
     * 
     * Commenting,
     * line 88 : // get all record of user from middleware where token is getting checked.
     * line 91 : // get pid from user request 
     * line 95 : // this error will be always shown so ignore it.
     */ 
    public function delete_photo(PhotoDeleteValidation $req)
    {
        try
        {
            $user_record = $req->user_data; 
            if(!empty($user_record))
            {
                $uid = $user_record->_id; 
                $pid = $req->photoID; 
                $coll = new MongoDatabaseConnection();
                $table = 'photos';
                $coll2 = $coll->db_connection();
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


    /***
     * @@ Function : User Searches A Photo / Image @@
     * 
     * Commenting,
     * line 135 : // get all record of user from middleware where token is getting checked.
     * line 138 : // get user id from middleware.
     * line 139 : // this error will be always shown so ignore it.
     * line 140 : // get validated data.
     * line 141 : // get validated data.
     * line 142 : // put all not null values in associative array in $put_data.
     * line 153 : // converts data in $objects into json and returns.
     * line 154 : // converts data in $objects into simplly toArray().
     */ 
    public function search_photos(PhotoSearchingValidation $req)
    {
        try
        {
            $user_record = $req->user_data;
            if(!empty($user_record))
            {
                $uid = $user_record->_id;
                $uuid = new \MongoDB\BSON\ObjectId($uid);
                $date = $req->date;  $time = $req->time;  $name = $req->name;
                $extension = $req->extension;  $accessors = $req->accessors;
                $put_data = [];
                if($uid)            { $put_data['user_id'] = $uuid; }
                if($name)           { $put_data['photo_name'] = $name; }
                if($extension)      { $put_data['Photo_extension'] = $extension; }
                if($date)           { $put_data['date'] = $date; }
                if($time)           { $put_data['time'] = $time; }
                if($accessors)      { $put_data['access'] = $accessors; }
                $coll = new MongoDatabaseConnection();
                $table = 'photos';
                $coll2 = $coll->db_connection();
                $data = $coll2->$table->find($put_data);
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


    /***
     * @@ Function : User Remove Private Access To Make Image Public / Hidden @@
     * 
     * Commenting,
     * line 189 : // get access value in $access variable from db. 
     * line 190 : // check if $access variable is private or not.
     * line 192 : // if private then reomve / unset and make it public or hidden.
     */ 
    public function remove_private_access($photoID, $uid)
    {
        $coll = new MongoDatabaseConnection();
        $table = 'photos';
        $coll2 = $coll->db_connection();
        $find = $coll2->$table->findOne([ '_id' => $photoID ]);
        $access = $find['access'];
        if($access == "private")
        {
            $coll2->$table->updateOne(array("_id" => $photoID, "user_id" => $uid), array('$unset'=>array('photo_access_to' => '')));
            return response()->json(['Message'=>'photo private access email removed'], 200);   
        }
    }     


    /***
     * @@ Function : User Makes Image / Photo Public @@
     * 
     * Commenting,
     * line 214 : // get all record of user from middleware where token is getting checked.
     * line 215 : // this error will be always shown so ignore it.
     * line 216 : // this error will be always shown so ignore it.
     * line 217 : // make user photo public $access = 'public'.
     * line 218 : // Function (remove_private_access). if image is already private then it will be updated to public.
     */ 
    public function make_photo_public(PhotoMakePublic $req)
    {
        try
        {
            $user_record = $req->user_data; 
            $uid = new \MongoDB\BSON\ObjectId($user_record->_id); 
            $photoID = new \MongoDB\BSON\ObjectId($req->photoID);
            $access = 'public';
            $this->remove_private_access($photoID, $uid);
            $coll = new MongoDatabaseConnection();
            $table = 'photos';
            $coll2 = $coll->db_connection();
            $find = $coll2->$table->findOne([ '_id' => $photoID ]);
            if(!empty($find))
            {
                $update = $coll2->$table->updateOne(array("_id" => $photoID, "user_id" => $uid), array('$set'=>array('access' => $access)));
                if(!empty($update))
                {
                    return response(['Message'=>'Photo Updated to Public'], 200);   
                }
                else
                {
                    return response()->json(['Message'=>'You are not allowed to update someone else image.'], 404);
                }
            }
            else
            {
                return response()->json(['Message'=>'Image / Photo does not exists.'], 404);
            }
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }


    /***
    * @@ Function : User Makes Image / Photo Hidden@@
    * 
    * Commenting,
    * line 261 : // get all record of user from middleware where token is getting checked.
    * line 262 : // this error will be always shown so ignore it.
    * line 263 : // this error will be always shown so ignore it.
    * line 264 : // make user photo public $access = 'hidden'.
    * line 265 : // Function (remove_private_access). if image is already private then it will be updated to hidden.
    */ 
    public function make_photo_hidden(PhotoMakePublic $req)
    {
        try
        {
            $user_record = $req->user_data;
            $uid = new \MongoDB\BSON\ObjectId($user_record->_id); 
            $photoID = new \MongoDB\BSON\ObjectId($req->photoID);
            $access = 'hidden';
            $this->remove_private_access($photoID, $uid);
            $coll = new MongoDatabaseConnection();
            $table = 'photos';
            $coll2 = $coll->db_connection();
            $update = $coll2->$table->updateOne(array("_id" => $photoID, "user_id" => $uid), array('$set'=>array('access' => $access)));
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


    /***
    * @@ Function : User Makes Image / Photo Private@@
    * 
    * Commenting,
    * line 303 : // get all record of user from middleware where token is getting checked.
    * line 304 : // this error will be always shown so ignore it.
    * line 305 : // this error will be always shown so ignore it.
    * line 306 : // make user photo public $access = 'private'.    
    * line 307 : // get emails for private photo in a variable.
    * line 308 : // seprate those emails in array.
    * line 313 : // update the access field in db to private.
    * line 314 : // way-1 to update all access to emails in db through mongodb query.
    * line 317 : // way-2 to update all access to emails in db thorugh foreach loop mongodb query.
    */
    public function make_photo_private(PhotoMakePrivate $req)
    {
        try
        {
            $user_record = $req->user_data;
            $uid = new \MongoDB\BSON\ObjectId($user_record->_id); 
            $photoID = new \MongoDB\BSON\ObjectId($req->photoID);
            $access = 'private';
            $assess_emails = $req->assess_emails; 
            $names_arr = explode(',', $assess_emails); 
            $coll = new MongoDatabaseConnection();
            $table = 'photos';
            $coll2 = $coll->db_connection();
            $update1 = $coll2->$table->updateOne(array("_id" => $photoID, "user_id" => $uid), array('$set'=>array('access' => $access)));
            // $update2 = $coll2->$table->updateOne(array("_id" => $photoID, "user_id" => $uid), array('$set'=>array('access_to' => $key)));
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


    /***
    * @@ Function : User Removes Access(emails for private images) Of Other Users For His Image Link Access@@
    * 
    * Commenting,
    * line 349 : // get all record of user from middleware where token is getting checked.
    * line 350 : // this error will be always shown so ignore it.
    * line 351 : // this error will be always shown so ignore it.
    * line 352 : // get validated email from user to remove private image access.
    * line 357 : // get $access to confirm the that the image is private or not.
    */    
    public function remove_specfic_email(RemovePrivateSpecficEmail $req)
    {
        try
        {
            $user_record = $req->user_data; 
            $uid = new \MongoDB\BSON\ObjectId($user_record->_id); 
            $photoID = new \MongoDB\BSON\ObjectId($req->photoID);
            $email = $req->email;
            $coll = new MongoDatabaseConnection();
            $table = 'photos';
            $coll2 = $coll->db_connection();
            $find = $coll2->$table->findOne([ '_id' => $photoID ]);
            $access = $find['access'];
            if($access == "private")
            {
                $coll2->$table->updateOne(array("_id" => $photoID, "user_id" => $uid, "access" => $access), array('$pull'=>array('photo_access_to' => $email)));
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


    /***
    * @@ Function : Generating Link Of The Image @@
    * 
    * Commenting,
    * line 387 : // this error will be always shown so ignore it.
    * line 394 : // get link path in $link.
    * line 395 : // return $link in response->json($link).
    */
    public function get_a_shareable_link(GetAShareableLink $req)
    {
        try
        {
            $photoID = new \MongoDB\BSON\ObjectId($req->photoID); // this error will be always shown so ignore it.
            $coll = new MongoDatabaseConnection();
            $table = 'photos';
            $coll2 = $coll->db_connection();
            $find = $coll2->$table->findOne([ '_id' => $photoID ]);
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

    
    /***
    * @@ Function : Displaying Link Of The Image @@
    * 
    * Commenting,
    * line 421 : // get data in $data from link middleware.
    * line 422 : // get data from $data in seprate variables ($access, $link, $permission).
    * line 426 : // create a new path again and get it in $path and check the if else-if conditions.
    */
    public function show_link(ShowShareableLinkValidation $req, $show_link)
    {
        try 
        {
            $data = $req->image_link;
            $access = $data['access']; $link = $data['link']; $permission = $data['permission'];
            $link = explode('/',$req->link);            
            $new_link = $link[6];            
            $headers = ["Cache-Control" => "no-store, no-cache, must-revalidate, max-age=0"];
            $path = storage_path("app/images".'/'.$new_link);
            if($access == 'public' && $permission == "1") // display public image
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
            else if($access == 'hidden' && $permission == "0")  // display hidden image
            {
                $msg = "Not Allowed / Hidden.";
                return response()->json(['Message' => $msg]);
            }
            else if($access == 'private' && $permission == 1) // display private image
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
            else if($access == 'private'&& $permission == "0") // display private image
            {
                $msg = "Not Allowed Too See.";
                return response()->json(['Message' => $msg]);
            }          
        }
        catch(\Exception $show_error)
        {
            return response()->json(['Error' => $show_error->getMessage()], 500);
        }
    }
}
