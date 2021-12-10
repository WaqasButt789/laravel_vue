<?php

namespace App\Services;
use MongoDB\Client as mongo;

class DataBaseConnection{

public function get_connection($table)
{
    $db="imageHosting";
    $conn= (new mongo)->$db->$table;
    return $conn;

}

}
