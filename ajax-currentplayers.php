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

// Get list of current layers
$sql = "SELECT P.player_id, concat(kit_number, ' - ', fname, ' ', lname) AS player_info FROM Club C, ClubPlayer CP, Player P WHERE C.club_id = CP.club_id AND CP.player_id = P.player_id AND C.club_id = " . $club_id . " ORDER BY kit_number;";
$result = $conn->query($sql);

if ($result !== false) {
    // Populate a dropdown menu with players
    echo '<option value="" selected disabled hidden></option>';
    foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
        echo "<option value=" . htmlspecialchars($row["player_id"]) . ">" . htmlspecialchars($row["player_info"]) . "</option>";
    }
} else {
    echo '<option value="" selected disabled hidden>Invalid club selected</option>';
}

// Close connection
$conn->close();

?>