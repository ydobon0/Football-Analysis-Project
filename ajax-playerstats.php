<?php
// Get team id from request
$player_id = $_REQUEST["player_id"];

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
$sql = sprintf("select count(*) as matches_played, sum(goals) as goals, sum(goals)/count(*) as goals_per_game, sum(assists) as assists, sum(yellow_card) as yellow_cards, sum(red_card) as red_cards from FixturePlayerStats FPS where FPS.player_id = %d;", $player_id);
$result = $conn->query($sql);

if ($result !== false) {
    $row = $result->fetch_all(MYSQLI_ASSOC)[0];

    if ($row['matches_played'] == 0) {
        echo '<tr><td>No match data for this player</td></tr>';
    } else {
        echo sprintf("<tr><td>Total matches played</td><td>%d</td></tr>", $row['matches_played']);
        echo sprintf("<tr><td>Goals</td><td>%d</td></tr>", $row['goals']);
        echo sprintf("<tr><td>Goals/game</td><td>%f</td></tr>", $row['goals_per_game']);
        echo sprintf("<tr><td>Assists</td><td>%d</td></tr>", $row['assists']);
        echo sprintf("<tr><td>Yellow cards</td><td>%d</td></tr>", $row['yellow_cards']);
        echo sprintf("<tr><td>Red cards</td><td>%d</td></tr>", $row['red_cards']);
    }
}

// Close connection
$conn->close();

?>