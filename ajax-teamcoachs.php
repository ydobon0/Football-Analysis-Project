<?php
// Get team id from request
$club_id = $_REQUEST["club_id"];

// Connection settings
$servername = "localhost";
$username = "server";
$password = "serverpassword";
$dbname = "football";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate club id
$invalid = false;
$sql = "SELECT club_id FROM Club WHERE club_id = " . $club_id . ";";
$result = $conn->query($sql);
if (count($result->fetch_all()) != 1) {
    $invalid = true;
}
if ($invalid) {
    echo '<option value="" selected disabled hidden>Invalid club selected</option>';
    return;
}

// Get current coach
$sql = "SELECT M.manager_id, concat(fname, ' ', lname) AS manager_info FROM Manager M, ClubManager CM WHERE M.manager_id = CM.manager_id AND CM.club_id = " . $club_id . ";";
$result = $conn->query($sql);
if ($result !== false) {
    // Populate a dropdown menu with current coach (there should only be one)
    echo '<option value="" selected disabled hidden></option>';
    foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
        echo "<option value=" . htmlspecialchars($row["manager_id"]) . ">" . htmlspecialchars($row["manager_info"]) . "</option>";
    }
} else {
    return;
}

// Get previous coaches
$sql = "SELECT manager_id, concat(fname, ' ', lname) AS manager_info FROM Manager WHERE last_club_id = " . $club_id . " AND manager_id NOT IN 
(SELECT M.manager_id FROM Manager M, ClubManager CM WHERE M.manager_id = CM.manager_id AND CM.club_id = " . $club_id . ");";
$result = $conn->query($sql);
if ($result !== false) {
    // Populate a dropdown menu with previous coaches
    echo '<option value="" selected disabled hidden></option>';
    foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
        echo "<option value=" . htmlspecialchars($row["manager_id"]) . ">" . htmlspecialchars($row["manager_info"]) . " (previous) </option>";
    }
} else {
    return;
}

// Close connection
$conn->close();

?>