<!DOCTYPE html>
<html>
<head>
    <title>Add a player</title>
</head>
<body>
<?php
// Get variables from request
$home_club_id = $_POST["home_club_id"];
$away_club_id = $_POST["away_club_id"];
$fixture_id = $_POST["fixture_id"];

// Verify all necessary variables are present
$insufficient = false;
if ($home_club_id === null) {
    echo "<p>Please choose a home team</p>";
    $insufficient = true;
}
if ($away_club_id === null) {
    echo "<p>Please select an away team</p>";
    $insufficient = true;
}
if ($fixture_id === null) {
    echo "<p>Please select a fixture</p>";
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
$sql = "SELECT club_id FROM Club WHERE club_id = " . $home_club_id . ";";
$result = $conn->query($sql);
if (count($result->fetch_all()) != 1) {
    echo "<p>Team does not exist in database</>";
    $invalid = true;
}
$sql = "SELECT club_id FROM Club WHERE club_id = " . $away_club_id . ";";
$result = $conn->query($sql);
if (count($result->fetch_all()) != 1) {
    echo "<p>Team does not exist in database</>";
    $invalid = true;
}
$sql = "SELECT fixture_id FROM Fixture WHERE fixture_id = " . $fixture_id . " AND home_club_id = " . $home_club_id . " AND away_club_id = " . $away_club_id . ";";
$result = $conn->query($sql);
if (count($result->fetch_all()) != 1) {
    echo "<p>Fixture does not exist in database</>";
    $invalid = true;
}
if ($invalid) {
    $conn->close();
    return;
}

// Start a transaction
$sql = "START TRANSACTION;";
$conn->query($sql);

// Delete linked substitutions
$sql = 'DELETE FROM Substitution WHERE fixture_id = ' . $fixture_id . ';';
if($conn->query($sql) == false) {
    echo "<p>Error deleting from database</p>";
    echo "<p>" . htmlspecialchars($conn->error) . "</p>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Delete linked player stats
$sql = 'DELETE FROM FixturePlayerStats WHERE fixture_id = ' . $fixture_id . ';';
if($conn->query($sql) == false) {
    echo "<p>Error deleting from database</p>";
    echo "<p>" . htmlspecialchars($conn->error) . "</p>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Delete fixture
$sql = 'DELETE FROM Fixture WHERE fixture_id = ' . $fixture_id . ';';
if($conn->query($sql) == false) {
    echo "<p>Error deleting from database</p>";
    echo "<p>" . htmlspecialchars($conn->error) . "</p>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Commit transaction
$conn->query("COMMIT;");

// Close connection
$conn->close();

echo "<p>Deleted from database</p>"
?>
<p>You will be redirected in 3 seconds</p>
<script>
    var timer = setTimeout(function() {
        window.location='delete-fixture.php'
    }, 3000);
</script>
</body>
</html>