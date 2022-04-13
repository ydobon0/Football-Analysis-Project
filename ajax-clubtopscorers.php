<?php
// apparently mysqli keeps losing my order so we gotta define our own php sorting function
function row_compare($a, $b) {
    return $b["total_goals"] - $a["total_goals"];
}

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

// Get list of top scorers
$sql = "select P.player_id, concat(fname, ' ', lname) as player_name, sum(goals) as total_goals from Player P, ClubPlayer CP, FixturePlayerStats FPS where P.player_id = CP.player_id and CP.club_id = " . $club_id . " and FPS.player_id = P.player_id group by P.player_id, player_name order by total_goals desc;";
$result = $conn->query($sql);

// Populate table
if ($result !== false) {
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    uasort($rows, 'row_compare');
    $i = 1;
    echo "<tr><th>Rank</th><th>Player</th><th>Goals</th></tr>";
    foreach ($rows as $row) {
        echo sprintf("<tr><td>%d</td><td>%s</td><td>%d</td></tr>", $i, $row["player_name"], $row["total_goals"]);
        $i++;
    }
} else {
    echo '<tr><td>Invalid club selected: ' . htmlspecialchars($club_id) .  '</td></tr>';
    echo "<tr><td>Reason: " . htmlspecialchars($conn->error) . "</td></tr>";

}

// Close connection
$conn->close();

?>