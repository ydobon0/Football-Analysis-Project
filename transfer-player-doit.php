<!DOCTYPE html>
<html>
<head>
    <title>Transfer a player</title>
</head>
<body>
<?php
// Get variables from request
$old_club_id = $_POST["old_club_id"];
$new_club_id = $_POST["new_club_id"];
$player_id = $_POST["player_id"];
$number = $_POST["number"];

// Verify all necessary variables are present
$insufficient = false;
if ($old_club_id === null) {
    echo "<p>Please choose an old team</p>";
    $insufficient = true;
}
if ($new_club_id === null) {
    echo "<p>Please choose a new team</p>";
    $insufficient = true;
}
if ($player_id === null) {
    echo "<p>Please select a player</p>";
    $insufficient = true;
}
if ($number === null) {
    echo "<p>Please choose a new number</p>";
    $insufficient = true;
}
if ($insufficient) {
    return;
}

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

// Verify values of input
$invalid = false;
$sql = "SELECT club_id FROM Club WHERE club_id = " . $old_club_id . ";";
$result = $conn->query($sql);
if (count($result->fetch_all()) != 1) {
    echo "<p>Old team does not exist in database</>";
    $invalid = true;
} else {
    $sql = "SELECT kit_number FROM ClubPlayer WHERE club_id = " . $old_club_id . " AND player_id = " . $player_id . ";";
    $result = $conn->query($sql);
    if (count($result->fetch_all()) != 1) {
        echo "<p>Player is not on old team</>";
        $invalid = true;
    }
}
$sql = "SELECT club_id FROM Club WHERE club_id = " . $new_club_id . ";";
$result = $conn->query($sql);
if (count($result->fetch_all()) != 1) {
    echo "<p>New team does not exist in database</>";
    $invalid = true;
} else {
    $sql = "SELECT kit_number FROM ClubPlayer WHERE club_id = " . $new_club_id . " AND kit_number = " . $number . ";";
    $result = $conn->query($sql);
    if (count($result->fetch_all()) != 0) {
        echo "<p>Number already in use</>";
        $invalid = true;
    }
}
if ($invalid) {
    $conn->close();
    return;
}

// Start a transaction
$sql = "START TRANSACTION;";
$conn->query($sql);

// Unregister player from old team
$sql = "DELETE FROM ClubPlayer WHERE club_id = " . $old_club_id . " AND player_id = " . $player_id . ";";
if($conn->query($sql) == false) {
    echo "<p>Error removing player from old team</p>";
    echo "<p>" . htmlspecialchars($conn->error) . "</p>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Register player to new team
$sql = 'INSERT INTO ClubPlayer VALUES ("' . $player_id . '", "' . $new_club_id . '", "' . $number . '");';
if($conn->query($sql) == false) {
    echo "<p>Error adding player to new team</p>";
    echo "<p>" . htmlspecialchars($conn->error) . "</p>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Commit transaction
$conn->query("COMMIT;");

// Close connection
$conn->close();

echo "<p>Successfully transferred</p>"

?>
<p>You will be redirected in 3 seconds</p>
<script>
    var timer = setTimeout(function() {
        window.location='transfer-player.php'
    }, 3000);
</script>
</body>
</html>

