<!doctype html>
<html>
<head>
    <title>Team stats</title>
    <script>
        function getTeamStats(club_id) {
            if (club_id === "") {
                return;
            }
            getTopScorers(club_id);
            getSummary(club_id);
        }
    </script>
    <script>
        function getTopScorers(club_id) {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("top-scorer-table").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "ajax-clubtopscorers.php?club_id=" + club_id, true);
            xmlhttp.send();
        }
    </script>
    <script>
        function getSummary(club_id) {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("summary-table").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "ajax-clubsummary.php?club_id=" + club_id, true);
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
        }
    </script>
</head>
<body onload="clearInputs()">
<h2>Team stats</h2>
<select id="team-select" name="club_id" onchange="getTeamStats(this.value)">
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
<h3>Top scorers</h3>
<table id="top-scorer-table">

</table>

<h3>Summary</h3>
<table id="summary-table">

</table>
</body>
</html>