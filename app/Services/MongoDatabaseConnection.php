<?php

namespace App\Services;
use MongoDB\Client as mongo;

class MongoDatabaseConnection
{
    /***
     * @ Function : Making Connection With MongoDB Database @
     */
    function db_connection()
    {
        $collect = (new mongo)->ImageHosting;
        return $collect;
    }
}