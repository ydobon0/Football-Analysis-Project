<!doctype html>
<html>
<head>
    <title>Delete-fixture</title>
    <script>
        function getFixtures(home_club_id, away_club_id) {
            if (home_club_id === "" || away_club_id === "")
                return;
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("fixture-select").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "ajax-fixturesbetweenclubs.php?home_club_id=" + home_club_id + "&away_club_id=" + away_club_id, true);
            xmlhttp.send();
        }
    </script>
    <script>
        function clearInputs() {
            document.getElementById("home-club-select").value="";
            document.getElementById("away-club-select").value="";
            document.getElementById("fixture-select").innerHTML = '<option value="" selected disabled hidden></option>';
            document.getElementById("fixture-select").value="";
        }
    </script>
</head>
<body onload="clearInputs()">
<h2>Delete a fixture</h2>
<form action="delete-fixture-doit.php" method="post">
    <table>
        <tr>
            <td>Home team</td>
            <td>
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
                    echo '<select name="home_club_id" id="home-club-select" onchange="getFixtures(this.value, document.getElementById(\'away-club-select\').value)">';
                    echo '<option value="" selected disabled hidden>Select a team</option>';
                    while($row = $result->fetch_assoc()) {
                        echo "<option value=" . htmlspecialchars($row["club_id"]) . ">" . htmlspecialchars($row["name"]) . "</option>";
                    }
                    echo "</select>";
                } else {
                    echo "No results found";
                }

                // Close connection
                $conn->close();
                ?>
            </td>
        </tr>
        <tr>
            <td>Away team</td>
            <td>
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
                    echo '<select name="away_club_id" id="away-club-select" onchange="getFixtures(document.getElementById(\'home-club-select\').value, this.value)">';
                    echo '<option value="" selected disabled hidden>Select a team</option>';
                    while($row = $result->fetch_assoc()) {
                        echo "<option value=" . htmlspecialchars($row["club_id"]) . ">" . htmlspecialchars($row["name"]) . "</option>";
                    }
                    echo "</select>";
                } else {
                    echo "No results found";
                }

                // Close connection
                $conn->close();
                ?>
            </td>
        </tr>
        <tr>
            <td>Fixture:</td>
            <td>
                <select id="fixture-select" name="fixture_id">
                    <option value="" selected disabled hidden></option>
                </select>
            </td>
        </tr>
    </table>
    <button id="delete" type="submit">Delete</button>
    <button id="clear" type="button" onclick="clearInputs()">Clear</button>
</form>

</body>
</html>