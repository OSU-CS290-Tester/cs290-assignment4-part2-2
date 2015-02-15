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
if (isset($_POST['title'])) {//check for $_POST['title'] and set $title. Since title is required send error if not existing
  $title = $_POST['title'];
  $_SESSION['error']['title'] = 'none';
}
else {//title not sent. error
  $_SESSION['error']['title'] = 'required';
  header("Location: $redirect", true);
  die();
}
if (isset($_POST['category'])) {//if category is sent, set $category equal
  $category = $_POST['category'];
}

if (isset($_POST['length'])) {//if length is sent, check that length is a positive variable and set $length
  if ((!($_POST['length'] === '') && !($_POST['length'] === (string)(int)$_POST['length'])) || (int)$_POST['length'] < 0) {//not a positive integer
    $_SESSION['error']['length'] = 'error';
	header("Location: $redirect", true);
    die();
  } else { //length is an integer
    $length = (int)$_POST['length'];
	$_SESSION['error']['length'] = 'none';
	}
  }
else {
  $_SESSION['error']['length'] = 'none';
}

if (isset($category) && isset($length)) {
  if (!($stmt = $my_sqli->prepare("INSERT INTO movie_rental(name, category, length) VALUES (?, ?, ?)"))) {
    echo "Prepare statement failed: (" . $my_sqli->errno . ") " . $my_sqli->error . "<br>";
  }
  if (!$stmt->bind_param("ssi", $title, $category, $length)) {
    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error . "<br>";
  }
  if (!$stmt->execute()) {
    if ($stmt->errno === 1062) { //duplicate title
	  $_SESSION['error']['title'] = 'duplicate';
	  header("Location: $redirect", true);
      die();
	}
	else { //some other error
	  echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error . "<br>";
	}
  }
  if (!$stmt->close()) {
	echo "Close failed: (" . $stmt->errno . ") " . $stmt->error . "<br>";
  }
}
elseif (isset($category)) {
  if (!($stmt = $my_sqli->prepare("INSERT INTO movie_rental(name, category) VALUES (?, ?)"))) {
    echo "Prepare statement failed: (" . $my_sqli->errno . ") " . $my_sqli->error;
  }
  if (!$stmt->bind_param("ss", $title, $category)) {
    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  }
  if (!$stmt->execute()) {
    if ($stmt->errno === 1062) { //duplicate title
	  $_SESSION['error']['title'] = 'duplicate';
	  header("Location: $redirect", true);
      die();
	}
	$stmt->close();
  }
}
elseif (isset($length)) {
  if (!($stmt = $my_sqli->prepare("INSERT INTO movie_rental(name, length) VALUES (?, ?)"))) {
    echo "Prepare statement failed: (" . $my_sqli->errno . ") " . $my_sqli->error;
  }
  if (!$stmt->bind_param("si", $title, $length)) {
    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  }
  if (!$stmt->execute()) {
    if ($stmt->errno === 1062) { //duplicate title
	  $_SESSION['error']['title'] = 'duplicate';
	  header("Location: $redirect", true);
      die();
	}
	$stmt->close();
  }
}
else {
  if (!($stmt = $my_sqli->prepare("INSERT INTO movie_rental(name, length) VALUES (?)"))) {
    echo "Prepare statement failed: (" . $my_sqli->errno . ") " . $my_sqli->error;
  }
  if (!$stmt->bind_param("s", $title)) {
    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  }
  if (!$stmt->execute()) {
    if ($stmt->errno === 1062) { //duplicate title
	  $_SESSION['error']['title'] = 'duplicate';
	  header("Location: $redirect", true);
      die();
	}
	$stmt->close();
  }
}

//back to main page
header("Location: $redirect", true);
die();
?>