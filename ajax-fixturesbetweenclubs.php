<?php
// Get team id from request
$home_club_id = $_REQUEST["home_club_id"];
$away_club_id = $_REQUEST["away_club_id"];

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

// Start a transaction
$sql = "START TRANSACTION;";
$conn->query($sql);

// Clear existing temporary tables
$sql = "DROP TEMPORARY TABLE IF EXISTS fixture_home_data, fixture_away_data;";
if($conn->query($sql) == false) {
    echo "<option>Error clearing temporary tables from database</option>";
    echo "<option>" . htmlspecialchars($conn->error) . "</option>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Create temporary table for home stats
$sql = "CREATE TEMPORARY TABLE fixture_home_data SELECT F.fixture_id, F.week_number, sum(FPS.goals) as home_goals FROM Fixture F, FixturePlayerStats FPS, ClubPlayer CP WHERE F.fixture_id = FPS.fixture_id and CP.player_id = FPS.player_id and CP.club_id = F.home_club_id and F.home_club_id = " . $home_club_id . " AND F.away_club_id = " . $away_club_id . " GROUP BY fixture_id;";
if($conn->query($sql) == false) {
    echo "<option>Error retrieving data from database</option>";
    echo "<option>" . htmlspecialchars($conn->error) . "</option>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Create temporary table for away stats
$sql = "CREATE TEMPORARY TABLE fixture_away_data SELECT F.fixture_id, sum(FPS.goals) as away_goals FROM Fixture F, FixturePlayerStats FPS, ClubPlayer CP WHERE F.fixture_id = FPS.fixture_id and CP.player_id = FPS.player_id and CP.club_id = F.away_club_id and F.home_club_id = " . $home_club_id . " AND F.away_club_id = " . $away_club_id . " GROUP BY fixture_id;";
if($conn->query($sql) == false) {
    echo "<option>Error retrieving data from database</option>";
    echo "<option>" . htmlspecialchars($conn->error) . "</option>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Get list of fixtures between the two teams
$sql = "SELECT home.fixture_id, concat('Week ', home.week_number, ': ', home.home_goals, ' - ', away.away_goals) AS game_info FROM fixture_home_data home, fixture_away_data away where home.fixture_id = away.fixture_id;";
$result = $conn->query($sql);

if ($result !== false) {
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    // Populate a dropdown menu with weeks
    if (count($rows) == 0) {
        echo '<option value="" selected disabled>No fixtures between these two teams yet</option>';
    } else {
        echo '<option value="" selected disabled hidden></option>';
    }
    foreach ($rows as $row) {
        echo "<option value=" . htmlspecialchars($row["fixture_id"]) . ">" . htmlspecialchars($row["game_info"]) . "</option>";
    }
} else {
    echo "<option>Error retrieving data from database</option>";
    echo "<option>" . htmlspecialchars($conn->error) . "</option>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Clear existing temporary tables
$sql = "DROP TEMPORARY TABLE IF EXISTS fixture_home_data, fixture_away_data;";
if($conn->query($sql) == false) {
    echo "<option>Error clearing temporary tables from database</option>";
    echo "<option>" . htmlspecialchars($conn->error) . "</option>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Commit
$conn->query("COMMIT;");

// Close connection
$conn->close();

?>