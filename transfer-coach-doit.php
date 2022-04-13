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
$manager_id = $_POST["manager_id"];

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
if ($manager_id === null) {
    echo "<p>Please select a coach</p>";
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
}
$sql = "SELECT club_id FROM Club WHERE club_id = " . $new_club_id . ";";
$result = $conn->query($sql);
if (count($result->fetch_all()) != 1) {
    echo "<p>New team does not exist in database</>";
    $invalid = true;
}
if ($invalid) {
    $conn->close();
    return;
}

// Start a transaction
$sql = "START TRANSACTION;";
$conn->query($sql);

// Remove coach from old team
$sql = "DELETE FROM ClubManager WHERE club_id = " . $old_club_id . " AND manager_id = " . $manager_id . ";";
if($conn->query($sql) == false) {
    echo "<p>Error removing coach from old team</p>";
    echo "<p>" . htmlspecialchars($conn->error) . "</p>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Check if there is a coach for the new team (and if so, remove them)
$sql = "SELECT M.manager_id, concat(M.fname, ' ', M.lname) AS manager_info FROM Manager M, ClubManager CM WHERE M.manager_id = CM.manager_id AND CM.club_id = " . $new_club_id . ";";
$result = $conn->query($sql);
foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
    $conn->query("DELETE FROM ClubManager WHERE club_id = " . $new_club_id . " AND manager_id = " . $row["manager_id"] . ";");
    echo "<p>Previous coach " . htmlspecialchars($row["manager_info"]) . " removed from the new team in the process.</p>";
}

// Add coach to new team
$sql = 'INSERT INTO ClubManager VALUES ("' . $manager_id . '", "' . $new_club_id . '");';
if($conn->query($sql) == false) {
    echo "<p>Error adding coach to new team</p>";
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
        window.location='transfer-coach.php'
    }, 3000);
</script>
</body>
</html>

