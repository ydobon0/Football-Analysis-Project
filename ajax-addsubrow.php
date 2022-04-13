<?php
// Get team id from request
$next_id = $_REQUEST["next_id"];
$club_id = $_REQUEST["club_id"];
$is_home = $_REQUEST['is_home'];

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

echo sprintf("<tr id='sub_%s_%d'><td>Off Player: </td><td><select name='sub_%s_%d_off'>", $is_home == "true" ? 'h' : 'a', $next_id, $is_home == "true" ? 'h' : 'a', $next_id);

// Get list of current layers
$sql = "SELECT P.player_id, concat(kit_number, ' - ', fname, ' ', lname) AS player_info FROM Club C, ClubPlayer CP, Player P WHERE C.club_id = CP.club_id AND CP.player_id = P.player_id AND C.club_id = " . $club_id . " ORDER BY kit_number;";
$result = $conn->query($sql);

if ($result !== false) {
    // Populate a dropdown menu with players
    echo "<option value='' selected disabled hidden></option>";
    foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
        echo sprintf("<option value='%d'>%s</option>", $row['player_id'], $row['player_info']);
    }
} else {
    echo '<tr><td>Invalid club selected</td></tr>';
}

echo sprintf("</select></td><td>On player:</td><td><select name='sub_%s_%d_on'>", $is_home == "true" ? 'h' : 'a', $next_id);

// Get list of current layers
$sql = "SELECT P.player_id, concat(kit_number, ' - ', fname, ' ', lname) AS player_info FROM Club C, ClubPlayer CP, Player P WHERE C.club_id = CP.club_id AND CP.player_id = P.player_id AND C.club_id = " . $club_id . " ORDER BY kit_number;";
$result = $conn->query($sql);

if ($result !== false) {
    // Populate a dropdown menu with players
    echo "<option value='' selected disabled hidden></option>";
    foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
        echo sprintf("<option value='%d'>%s</option>", $row['player_id'], $row['player_info']);
    }
} else {
    echo '<tr><td>Invalid club selected</td></tr>';
}

echo sprintf("</select></td><td>Time (minutes)</td><td><input type='number' name='sub_%s_%d_time' min='0'/></td></tr>",$is_home == "true" ? 'h' : 'a', $next_id);

// Close connection
$conn->close();

?>