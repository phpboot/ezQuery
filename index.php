<?php
require_once('connector/classes/Connection.php');

// create a connection
$conn = new \connector\classes\Connection();


//Get Database Results as object
$conn->select('orders')
    ->where('name','=','Orlando')
    ->getObject();

echo $conn->getCount();

?>