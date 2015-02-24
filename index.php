<?php
require_once('connector/classes/Connection.php');

// create a connection
$conn = new \connector\classes\Connection();


//Get Database Results as object
$results = $conn
    ->select('orders')
    ->getObject();

echo $conn->getCount();
echo $conn->getQuery();

foreach($results as $result){
    echo $result->id;
    echo $result->name;
    echo $result->order;
}

?>