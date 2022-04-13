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

// Start transaction just for good measure
$conn->query("BEGIN TRANSACTION;");

// Get matches played
$sql = sprintf("select count(*) as matches_played from Club C, Fixture F where C.club_id = %d and (C.club_id = F.home_club_id or C.club_id = F.away_club_id);", $club_id);
$result = $conn->query($sql);
if ($result !== false) {
    $matches_played = $result->fetch_all(MYSQLI_ASSOC)[0]['matches_played'];
} else {
    $conn->query("ROLLBACK;");
    return;
}

// Get total goals
$sql = sprintf("select sum(goals) as total_goals from (select goals from ClubPlayer CP, FixturePlayerStats FPS where CP.player_id = FPS.player_id and CP.club_id = %d union all select 0) goals_all;", $club_id);
$result = $conn->query($sql);
if ($result !== false) {
    $total_goals = $result->fetch_all(MYSQLI_ASSOC)[0]['total_goals'];
} else {
    $conn->query("ROLLBACK;");
    return;
}

// Get number of home goals
$sql = sprintf("select sum(goals) as home_goals from ClubPlayer CP, Player P, FixturePlayerStats FPS, Fixture F where CP.club_id = %d and CP.player_id = P.player_id and FPS.player_id = P.player_id and FPS.fixture_id = F.fixture_id and F.home_club_id = CP.club_id;", $club_id);
$result = $conn->query($sql);
if ($result !== false) {
    $home_goals = $result->fetch_all(MYSQLI_ASSOC)[0]['home_goals'];
    if ($home_goals == null) {
        $home_goals = 0;
    }
} else {
    $conn->query("ROLLBACK;");
    return;
}

// Get number of away goals
$sql = sprintf("select sum(goals) as away_goals from ClubPlayer CP, Player P, FixturePlayerStats FPS, Fixture F where CP.club_id = %d and CP.player_id = P.player_id and FPS.player_id = P.player_id and FPS.fixture_id = F.fixture_id and F.away_club_id = CP.club_id;", $club_id);
$result = $conn->query($sql);
if ($result !== false) {
    $away_goals = $result->fetch_all(MYSQLI_ASSOC)[0]['away_goals'];
    if ($away_goals == null) {
        $away_goals = 0;
    }
} else {
    $conn->query("ROLLBACK;");
    return;
}

// Get number of goals conceeded
$sql = sprintf("select sum(goals) as goals_conceeded from (select goals from ClubPlayer CP, Fixture F, FixturePlayerStats FPS where F.home_club_id = %d and F.away_club_id = CP.club_id and FPS.player_id = CP.player_id union all select goals from ClubPlayer CP, Fixture F, FixturePlayerStats FPS where F.away_club_id = %d and F.home_club_id = CP.club_id and FPS.player_id = CP.player_id union all select 0) goals_all;", $club_id, $club_id);
$result = $conn->query($sql);
if ($result !== false) {
    $goals_conceeded = $result->fetch_all(MYSQLI_ASSOC)[0]['goals_conceeded'];
} else {
    $conn->query("ROLLBACK;");
    return;
}

// Calculate goals/game
$goals_per_game = $matches_played === 0 ? NAN : $total_goals / $matches_played;

// Get number of assists
$sql = sprintf("select sum(assists) as assists from (select assists from ClubPlayer CP, Player P, FixturePlayerStats FPS where CP.club_id = %d and CP.player_id = P.player_id and FPS.player_id = P.player_id union all select 0) assists_all;", $club_id);
$result = $conn->query($sql);
if ($result !== false) {
    $assists = $result->fetch_all(MYSQLI_ASSOC)[0]['assists'];
} else {
    $conn->query("ROLLBACK;");
    return;
}

// Get ball possession
$sql = sprintf("select avg(if(F.home_club_id = C.club_id, home_poss, 100 - home_poss)) as ball_possession from Club C, Fixture F where C.club_id = %d and (F.home_club_id = C.club_id or F.away_club_id = C.club_id);", $club_id);
$result = $conn->query($sql);
if ($result !== false) {
    $ball_possession = $result->fetch_all(MYSQLI_ASSOC)[0]['ball_possession'];
    if ($ball_possession == null) {
        $ball_possession = NAN;
    }
} else {
    $conn->query("ROLLBACK;");
    return;
}

// Get pass accuracy
$sql = sprintf("select sum(pass_succeeded)/sum(pass_total) * 100 as pass_accuracy from ClubPlayer CP, FixturePlayerStats FPS where CP.player_id = FPS.player_id and CP.club_id = %d;", $club_id);
$result = $conn->query($sql);
if ($result !== false) {
    $pass_accuracy = $result->fetch_all(MYSQLI_ASSOC)[0]['pass_accuracy'];
    if ($pass_accuracy == null) {
        $pass_accuracy = NAN;
    }
} else {
    $conn->query("ROLLBACK;");
    return;
}

// Get yellow cards
$sql = sprintf("select sum(yellow_card) as yellow_cards from ClubPlayer CP, FixturePlayerStats FPS where CP.player_id = FPS.player_id and CP.club_id = %d;", $club_id);
$result = $conn->query($sql);
if ($result !== false) {
    $yellow_cards = $result->fetch_all(MYSQLI_ASSOC)[0]['yellow_cards'];
} else {
    $conn->query("ROLLBACK;");
    return;
}

// Get yellow cards per game
$yellow_cards_per_game = $matches_played == 0 ? NAN : $yellow_cards / $matches_played;

// Commit
$conn->query("COMMIT;");

// Close connection
$conn->close();

echo sprintf("<tr><td>Matches</td><td>%d</td></tr>", $matches_played);
echo sprintf("<tr><td>Total goals scored</td><td>%d</td></tr>", $total_goals);
echo sprintf("<tr><td>Home goals</td><td>%d</td></tr>", $home_goals);
echo sprintf("<tr><td>Away goals</td><td>%d</td></tr>", $away_goals);
echo sprintf("<tr><td>Goals conceded</td><td>%d</td></tr>", $goals_conceeded);
echo sprintf("<tr><td>Assists</td><td>%d</td></tr>", $assists);
echo sprintf("<tr><td>Goals/game</td><td>%f</td></tr>", $goals_per_game);
echo sprintf("<tr><td>Ball possession</td><td>%f%%</td></tr>", $ball_possession);
echo sprintf("<tr><td>Accurate passes</td><td>%f%%</td></tr>", $pass_accuracy);
echo sprintf("<tr><td>Yellow cards/game</td><td>%f</td></tr>", $yellow_cards_per_game);

?>