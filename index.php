<?php
require_once('connector/classes/Connection.php');

// create a connection
$conn = new \connector\classes\Connection();


//Insert Data to the database
/*
$conn->insert('orders', array(
    'order' => 'Pizza',
    'name'  => 'Cristobal'
))->save();
*/


/*
//Select Data from the database
$conn->select('orders')->getJson();


//Delete From database
$conn ->delete('orders')
    ->where('id','=','46')
    ->destroy();
*/

$conn->update('orders', array(
    'order' => 'Pollo',
    'name'  => 'Mongo'
))->where('id', '=', '61')->save();


echo $conn->getQuery();
$conn->getErrors();

?>