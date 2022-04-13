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

// Get list of used kit numbers
$sql = "SELECT DISTINCT kit_number FROM ClubPlayer WHERE club_id = " . $club_id . " ORDER BY kit_number;";
$result = $conn->query($sql);


if ($result !== false) {
    // Invert list to get free kit numbers
    $free_nums = array();
    $kit_num = 1;
    $used_num = $result->fetch_assoc()["kit_number"];
    while ($kit_num <= 99) {
        if ($kit_num == $used_num) {
            $used_num = $result->fetch_assoc()["kit_number"];
        } else {
            $free_nums[] = $kit_num;
        }
        $kit_num += 1;
    }

    // Populate a dropdown menu with kit numbers
    echo '<option value="" selected disabled hidden></option>';
    foreach ($free_nums as $free_num) {
        echo "<option value=" . htmlspecialchars($free_num) . ">" . htmlspecialchars($free_num) . "</option>";
    }
} else {
    echo '<option value="" selected disabled hidden>Invalid club selected</option>';
}

// Close connection
$conn->close();

?>