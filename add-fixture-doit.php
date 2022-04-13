<!DOCTYPE html>
<html>
<head>
    <title>Add a fixture</title>
</head>
<body>
<?php

// Get basic required variables from request
$week_number = $_POST["week_number"];
$home_club_id = $_POST["home_club_id"];
$away_club_id = $_POST["away_club_id"];
$home_possession = $_POST["home_possession"];

// Verify all necessary variables are present
$insufficient = false;
if ($week_number === "") {
    echo "<p>Please select a week</p>";
    $insufficient = true;
}
if ($home_club_id === null) {
    echo "<p>Please enter a home team</p>";
    $insufficient = true;
}
if ($away_club_id === null) {
    echo "<p>Please enter a away team</p>";
    $insufficient = true;
}
if ($home_club_id == $away_club_id) {
    echo "<p>Please select different teams</p>";
    $insufficient = true;
}
if ($home_possession === "") {
    echo "<p>Please enter home possession</p>";
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
$sql = "SELECT club_id FROM Club WHERE club_id = " . $home_club_id . " or club_id = " . $away_club_id .  ";";
$result = $conn->query($sql);
if (count($result->fetch_all()) != 2) {
    echo "<p>At least one team does not exist in database</>";
    $invalid = true;
}
if ($invalid) {
    $conn->close();
    return;
}

// Start a transaction
$sql = "START TRANSACTION;";
$conn->query($sql);

// Get list of all potential participating players
$sql = sprintf('SELECT CP.player_id from ClubPlayer CP WHERE CP.club_id = %d or CP.club_id = %d', $home_club_id, $away_club_id);
$result = $conn->query($sql);
if ($result !== false) {
    $player_rows = $result->fetch_all(MYSQLI_ASSOC);
    $players = array();
    foreach( $player_rows as $row) {
        $players[] = $row['player_id'];
    }
} else {
    echo "<p>Error getting list of players</p>";
    echo "<p>" . htmlspecialchars($conn->error) . "</p>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Insert new fixture into Fixture
$sql = sprintf('INSERT INTO Fixture (week_number, home_club_id, away_club_id, home_poss) values (%d, %d, %d, %d)', $week_number, $home_club_id, $away_club_id, $home_possession);
if($conn->query($sql) == false) {
    echo "<p>Error adding fixture to database</p>";
    echo "<p>" . htmlspecialchars($conn->error) . "</p>";
    $conn->query("ROLLBACK;");
    $conn->close();
    return;
}

// Get new fixture id
$fixture_id = $conn->insert_id;

// Insert substitutions (home)
$i = 0;
while ($sub_off = $_POST[sprintf("sub_h_%d_off", $i)]) {
    $sub_on = $_POST[sprintf("sub_h_%d_on", $i)];
    $sub_time = $_POST[sprintf("sub_h_%d_time", $i)];
    if (!($sub_on && $sub_time)) {
        echo "<p>Incomplete substitution data</p>";
        $conn->query("ROLLBACK;");
        $conn->close();
        return;
    }

    $sql = sprintf('INSERT INTO Substitution (fixture_id, on_player_id, off_player_id, time_in_min) VALUES (%d, %d, %d, %d)', $fixture_id, $sub_off, $sub_on, $sub_time);
    if($conn->query($sql) == false) {
        echo "<p>Error adding substitution to database</p>";
        echo "<p>" . htmlspecialchars($conn->error) . "</p>";
        $conn->query("ROLLBACK;");
        $conn->close();
        return;
    }

    $i++;
}

// Insert substitutions (away)
$i = 0;
while ($sub_off = $_POST[sprintf("sub_a_%d_off", $i)]) {
    $sub_on = $_POST[sprintf("sub_a_%d_on", $i)];
    $sub_time = $_POST[sprintf("sub_a_%d_time", $i)];
    if (!($sub_on && $sub_time)) {
        echo "<p>Incomplete substitution data</p>";
        $conn->query("ROLLBACK;");
        $conn->close();
        return;
    }

    $sql = sprintf('INSERT INTO Substitution (fixture_id, on_player_id, off_player_id, time_in_min) VALUES (%d, %d, %d, %d)', $fixture_id, $sub_off, $sub_on, $sub_time);
    if($conn->query($sql) == false) {
        echo "<p>Error adding substitution to database</p>";
        echo "<p>" . htmlspecialchars($conn->error) . "</p>";
        $conn->query("ROLLBACK;");
        $conn->close();
        return;
    }

    $i++;
}

// Insert fixture player data
foreach ($players as $player_id) {
    if ($_POST[sprintf('pstat_%d_played', $player_id)] === 'on') {
        $minutes = $_POST[sprintf('pstat_%d_mins', $player_id)];
        $fouls = $_POST[sprintf('pstat_%d_fouls', $player_id)];
        $pass_success = $_POST[sprintf('pstat_%d_psuccess', $player_id)];
        $pass_total = $_POST[sprintf('pstat_%d_ptotal', $player_id)];
        $goals = $_POST[sprintf('pstat_%d_goals', $player_id)];
        $assists = $_POST[sprintf('pstat_%d_assists', $player_id)];
        $yellow_cards = $_POST[sprintf('pstat_%d_yellow', $player_id)];
        $red_card = $_POST[sprintf('pstat_%d_red', $player_id)] === 'on' ? 1 : 0;

        if (!($minutes !== '' && $fouls !== '' && $pass_success !== '' && $pass_total !== '' && $goals !== '' && $assists !== '' && $yellow_cards !== '')) {
            echo "<p>Incomplete player data for ". $player_id . "</p>";
            $conn->query("ROLLBACK;");
            $conn->close();
            return;
        }

        $sql = sprintf('INSERT INTO FixturePlayerStats VALUES (%d, %d, %d, %d, %d, %d, %d, %d, %d, %d)', $fixture_id, $player_id, $minutes, $fouls, $pass_success, $pass_total, $yellow_cards, $red_card, $goals, $assists);
        if($conn->query($sql) == false) {
            echo "<p>Error adding player stats to database</p>";
            echo "<p>" . htmlspecialchars($conn->error) . "</p>";
            $conn->query("ROLLBACK;");
            $conn->close();
            return;
        }
    }
}

// Commit transaction
$conn->query("COMMIT;");

// Close connection
$conn->close();

echo "<p>Successfully added to database</p>"
?>
<p>You will be redirected in 3 seconds</p>
<script>
    var timer = setTimeout(function() {
        window.location='add-fixture.php'
    }, 3000);
</script>
</body>
</html>