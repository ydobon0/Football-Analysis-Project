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

// Get list of current layers
$sql = "SELECT P.player_id, concat(kit_number, ' - ', fname, ' ', lname) AS player_info FROM Club C, ClubPlayer CP, Player P WHERE C.club_id = CP.club_id AND CP.player_id = P.player_id AND C.club_id = " . $club_id . " ORDER BY kit_number;";
$result = $conn->query($sql);

if ($result !== false) {
    // Populate a dropdown menu with players
    echo "<tr><th>Player</th><th>Played?</th><th>Minutes played</th><th>Fouls</th><th>Passes (successful)</th><th>Passes (total)</th><th>Goals</th><th>Assists</th><th>Yellow cards</th><th>Red card</th></tr>";
    foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
        echo sprintf("<tr id='player-%d-stats'>
                                <td>%s</td>
                                <td><input name='pstat_%d_played' class='played' type='checkbox' onclick='toggleStatRow(this.parentElement.parentElement.id, this.checked)'/></td>
                                <td class='toggleable'><input name='pstat_%d_mins'  class='mins_played' type='number' min='0' max='130' disabled/></td>
                                <td class='toggleable'><input name='pstat_%d_fouls' class='fouls' type='number' min='0' disabled/></td>
                                <td class='toggleable'><input name='pstat_%d_psuccess' class='pass_success' type='number' min='0' disabled/></td>
                                <td class='toggleable'><input name='pstat_%d_ptotal' class='pass_total' type='number' min='0' disabled/></td>
                                <td class='toggleable'><input name='pstat_%d_goals' class='goals' type='number' min='0' disabled/></td>
                                <td class='toggleable'><input name='pstat_%d_assists' class='assists' type='number' min='0' disabled/></td>
                                <td class='toggleable'><input name='pstat_%d_yelow' class='yellow_cards' type='number' min='0' max='2' disabled/></td>
                                <td class='toggleable'><input name='pstat_%d_red' class='red_card' type='checkbox' disabled/></td>
                                </tr>", $row['player_id'], $row['player_info'], $row['player_id'], $row['player_id'], $row['player_id'], $row['player_id'], $row['player_id'], $row['player_id'], $row['player_id'], $row['player_id'], $row['player_id']);
    }
} else {
    echo '<tr><td>Invalid club selected</td></tr>';
}

// Close connection
$conn->close();

?>