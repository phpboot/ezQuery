<?php
require_once('connector/classes/Connection.php');

// create a connection
$conn = new \connector\classes\Connection();



$results2 = $conn->select('orders')->getObject();

?>