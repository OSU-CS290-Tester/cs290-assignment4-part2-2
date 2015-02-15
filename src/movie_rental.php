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

//open $_SESSION to check for errors from add_movie.php
session_start();
?>

<!-- create HTML document with POST 'Add a Movie' form and start a movie kiosk table -->
<html>
<head>
  <meta charset="utf-8" />
  <title>Movie Rental Kiosk</title>
</head>
<body>

<!-- 'Add a Movie' interface POST Form-->
  <form action="add_movie.php" method="post">
    <h1>Add a Movie to the Kiosk</h1>
    <label>Enter movie title: </label>
	  <input type="text" name="title" required oninvalid="this.setCustomValidity('This is a required field. Please enter a title before adding movie to kiosk.')" oninput="setCustomValidity('')"/> <br>
<?php //check for error from add_movie.php
      if ((isset($_SESSION['error']['title'])) && ($_SESSION['error']['title'] == 'required')) {
	    echo "<p style='color:red'>This is a required field. Please enter a title before adding movie to kiosk.</p>";
	  }
	  elseif ((isset($_SESSION['error']['title'])) && ($_SESSION['error']['title'] == 'duplicate')) {
	    echo "<p style='color:red'>Movie already exists in kiosk. Please add a new movie.</p>";
	  }
	  //reset to not an error
	  $_SESSION['error']['title'] = 'none';
?>	  
	<label>Enter movie category: </label>
	  <input type="text" name="category" placeholder="Drama, Comedy, etc" /> <br>
	<label>Enter movie length (in whole minutes): </label>
	  <input type="number" name="length" min='1' placeholder="120" oninvalid="this.setCustomValidity('The movie length must be a positive nonzero integer.')" oninput="setCustomValidity('')"/> <br>
<?php //check for error from add_movie.php
      if ((isset($_SESSION['error']['length'])) && ($_SESSION['error']['length'] == 'error')) {
	    echo "<p style='color:red'>Length must be a positive nonzero integer value.</p>";
	  }
	  //reset to not an error
	  $_SESSION['error']['length'] = 'none';
?>	 
	<button>Add Movie</button>
  </form>
  <br><br>
  <table border="1">
	<caption>Movie Kiosk</caption>
	<tr>
	  <th>Title</th>
	  <th>Category</th>
	  <th>Length (mins)</th>
	  <th>Movie Status</th>
	  <th></th>
	</tr>
    
<?php
//php function to create table from database
//prepare statement to SELECT required columns
//first need to check for $_SESSION['filter'] variable
if(!(isset($_SESSION['filter'])) || $_SESSION['filter'] == 'All Movies') {//no filter set so pull all results
  if (!($stmt = $my_sqli->prepare("SELECT name, category, length, rented FROM movie_rental ORDER BY name"))) {
    echo "Prepare statement failed: (" . $my_sqli->errno . ") " . $my_sqli->error . "<br>";
  }
}
else {//filter is set
  $filter = $_SESSION['filter'];
  if (!($stmt = $my_sqli->prepare("SELECT name, category, length, rented FROM movie_rental WHERE category = '$filter' ORDER BY name"))) {
    echo "Prepare statement failed: (" . $my_sqli->errno . ") " . $my_sqli->error . "<br>";
  }
}

//execute
if (!$stmt->execute()) {
  echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error . "<br>";
}
//bind results to php variables
if (!$stmt->bind_result($title, $category, $length, $rented)) {
  echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error . "<br>";
}

//fetch results and input/echo into table
while ($stmt->fetch()) {
  echo "<tr>
    <td>$title</td>";
  if ($category == null) {
    echo "<td>Uncategorized</td>";
  } else {
	echo "<td>$category</td>";
  }
  if ($length > 0) {
    echo "<td>$length</td>";
  } else {
    echo "<td>Unknown Length</td>";
  }
  if ($rented == 0) {//create <td> stating Available and create <td> with Check Out form to POST to check_out.php
    echo "<td>Available</td>
	<td><form action='check_out.php' method='post'>
      <input type='hidden' name='row_title' value='$title'>
	  <button>Check Out</button>
	</form>
    </td>";
  } else {//create <td> stating Checked Out and create <td> with Check in form to POST to check_in.php
    echo "<td>Checked Out</td>
	<td><form action='check_in.php' method='post'>
      <input type='hidden' name='row_title' value='$title'>
	  <button>Check In</button>
	</form>
    </td>";
  }
  //create <td> Delete form to POST to delete_row.php
  echo "<td><form action='delete_row.php' method='post'>
    <input type='hidden' name='row_title' value='$title'>
	<button>Delete</button>
  </form>
  </td>
  </tr>";
}
//close statement
$stmt->close(); 

//add delete all to footer
echo "<tfoot>
  <tr>
    <td><form action='delete_all.php' method='post'>
	<button>Delete All Videos</button>
  </form>
  </td>";

//php function to create table from database
//prepare statement to SELECT required columns
if (!($stmt = $my_sqli->prepare("SELECT DISTINCT category FROM movie_rental"))) {
  echo "Prepare statement failed: (" . $my_sqli->errno . ") " . $my_sqli->error . "<br>";
}
//execute
if (!$stmt->execute()) {
  echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error . "<br>";
}
//bind results to php variables
if (!$stmt->bind_result($category)) {
  echo "Bind failed: (" . $stmt->errno . ") " . $stmt->error . "<br>";
}

//create filter dropdown and then fetch categories and input/echo into dropdown
//default so that "selected" option is current filter used on table
echo "<td><form action='filter.php' method='post'>
      <select name='filter'>";
if (!(isset($_SESSION['filter'])) || $_SESSION['filter'] == 'All Movies') {
  echo "<option selected>All Movies</option>";
} else {
  echo "<option>All Movies</option>";
}
while ($stmt->fetch()) {
  if (!($category == null)) {
    if (isset($_SESSION['filter']) && $_SESSION['filter'] == $category) {//current category is the existing filter
	  echo "<option selected>$category</option>";
	} else {
      echo "<option>$category</option>";
	}
  }
}
echo "</select>
<button>Filter Movie Listings</button>
</form>
</td>";
?>

<!-- close rest of HTML document -->
    </tr>
    </tfoot>
  </table>
</body>
</html>