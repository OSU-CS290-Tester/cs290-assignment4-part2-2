<?php
//error reporting. comment out once code is working
error_reporting(E_ALL);
ini_set('display_errors', 1);

//connect to database
include 'stored_info.php';
$my_sqli = new mysqli("oniddb.cws.oregonstate.edu", "walzma-db", $db_password, "walzma-db");
if($my_sqli->connect_errno) {
  echo "Failed to connect to MySQL: (" . $my_sqli->connect_errno . ") " . $my_sqli->connect_error;
}

//open $_SESSION for errors reporting
session_start();
$redirect = "http://web.engr.oregonstate.edu/~walzma/movie_rental.php";

//pull data from POST request
if (isset($_POST['filter'])) {//check for $_POST['filter'] and set $_SESSION['filter'].
  $_SESSION['filter'] = $_POST['filter'];
}

//back to main page
header("Location: $redirect", true);
die();
?>