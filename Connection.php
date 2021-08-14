<?php

$dbServename = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "crossword_db";

$conn = new mysqli($dbServename, $dbUsername, $dbPassword, $dbName);

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
?>