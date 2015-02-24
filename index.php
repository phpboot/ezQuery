<?php
require_once('connector/classes/Connection.php');

// create a connection
$conn = new \connector\classes\Connection();


//Get Database results as an object
// $conn->select('orders')->getDatabaseObject();

//Get Database Results as Json
$conn->select('orders',['id','name','order'])
    ->where('name','=','Javier')
    ->orWhere('name','=','orlando')
    ->getPreparedArray();
?>