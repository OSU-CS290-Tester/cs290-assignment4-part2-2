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

$redirect = "http://web.engr.oregonstate.edu/~walzma/movie_rental.php";

//submit TRUNCATE to db
if (!$my_sqli->query("TRUNCATE movie_rental")) {
  echo "TRUNCATE failed: (" . $my_sqli->errno . ") " . $my_sqli->error;
}

//clear out existing session data
session_start();
$_SESSION = array();
session_destroy();

//redirect back to movie_rental.php
header("Location: $redirect", true);
die();
?>