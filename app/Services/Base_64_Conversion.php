<?php
namespace App\Services;

class Base_64_Conversion
{
    // base-64 conversion
    public function base_64_coonversion_of_image($base_image)
    {
        $base64_string =  $base_image; // get file in encoded form from the user(front-end)
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
        $profile_path=$url."/user/storage/images/".$fileName; // set file path in database
        $path=storage_path('app\\images').'\\'.$fileName; // set file path in project
        file_put_contents($path,base64_decode($image)); // put path in project storage

        $data = ['extension' => $extension, 'path' => $profile_path];

        return $data;

    }
}