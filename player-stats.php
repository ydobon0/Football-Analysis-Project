<!doctype html>
<html>
<head>
    <title>Player stats</title>
    <script>
        function getPlayers(club_id) {
            document.getElementById("stats-table").innerHTML = "";
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("player-select").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "ajax-currentplayers.php?club_id=" + club_id, true);
            xmlhttp.send();
        }
    </script>
    <script>
        function getPlayerStats(player_id) {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("stats-table").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "ajax-playerstats.php?player_id=" + player_id, true);
            xmlhttp.send();
        }
    </script>
    <style>
        th, td {
            border: 1px solid black;
        }
    </style>
    <script>
        function clearInputs() {
            document.getElementById("team-select").value="";
            document.getElementById("player-select").innerHTML = "";
            document.getElementById("player-select").value="";
            document.getElementById("stats-table").innerHTML = "";
        }
    </script>
</head>
<body onload="clearInputs()">
<h2>Player stats</h2>
<select id="team-select" name="club_id" onchange="getPlayers(this.value)">
    <option value="" selected disabled hidden>Select a team</option>
    <?php
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

    // Populate a dropdown menu with club names
    $sql = "SELECT club_id, name FROM Club;";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<option value=" . htmlspecialchars($row["club_id"]) . ">" . htmlspecialchars($row["name"]) . "</option>";
        }
    } else {
        echo "No results found";
    }

    // Close connection
    $conn->close();
    ?>
</select>
<select id="player-select" name="player_id" onchange="getPlayerStats(this.value)">

</select>

<table id="stats-table">

</table>
</body>
</html>