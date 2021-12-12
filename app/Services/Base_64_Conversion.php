<?php
namespace App\Services;

class Base_64_Conversion
{
    /***
     * @@ Function : Base-64 Conversion @@
     * 
     * Commentings,
     * line 22 : // get file in encoded form from the user(front-end).
     * line 23 : // get extension (// .jpg .png .pdf).
     * line 25 : // will get the image name but not original name because the original name is changed. 
     * line 26 : // get image without spaces.
     * line 27 : // get filename in extension.
     * line 28 : // get request type and change http tppe according to it.
     * line 38 : // set file path in database.
     * line 39 : // put path in project storage.
     */
    public function base_64_coonversion_of_image($base_image)
    {
        $base64_string =  $base_image; 
        $extension = explode('/', explode(':', substr($base64_string, 0, strpos($base64_string, ';')))[1])[1];
        $replace = substr($base64_string, 0, strpos($base64_string, ',')+1);
        $image = str_replace($replace, '', $base64_string); 
        $image = str_replace(' ', '+', $image); 
        $fileName = time().'.'.$extension;
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        {
            $url = "https://";
        }
        else
        {
            $url = "http://";
        }
        $url.= $_SERVER['HTTP_HOST'];
        $profile_path=$url."/user/storage/images/".$fileName; 
        $path=storage_path('app\\images').'\\'.$fileName; 
        file_put_contents($path,base64_decode($image)); 
        $data = ['extension' => $extension, 'path' => $profile_path];
        return $data;
    }
}