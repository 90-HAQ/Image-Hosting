<?php

namespace App\Services;
use MongoDB\Client as mongo;

class MongoDatabaseConnection
{
    function db_connection()
    {
        $collect = (new mongo)->ImageHosting;
        return $collect;
    }
}