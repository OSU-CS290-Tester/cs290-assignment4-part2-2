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

//pull data from POST request
if (isset($_POST['row_title'])) {//set $row_title equal
  $row_title = $_POST['row_title'];
}

//submit update to db
if (!($stmt = $my_sqli->prepare("UPDATE movie_rental SET rented = 0 WHERE name = ?"))) {
  echo "Prepare statement failed: (" . $my_sqli->errno . ") " . $my_sqli->error;
}
if (!$stmt->bind_param("s", $row_title)) {
  echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (!$stmt->execute()) {
  echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}

//close statement
$stmt->close();

//redirect back to movie_rental.php
header("Location: $redirect", true);
die();
?>