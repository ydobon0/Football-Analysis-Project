<!doctype html>
<html>
<head>
    <title>Add fixture</title>
    <script>
        function calculateAwayPossession(home_possession) {
            document.getElementById("away-possession").value = 100 - home_possession;
        }
    </script>
    <script>
        function populateHomeStatTable(club_id) {
            document.getElementById("substitution-table-home").innerHTML = "";
            document.getElementById("add-sub-home-button").disabled = false;
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("stat-table-home").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "ajax-inputstats.php?club_id=" + club_id, true);
            xmlhttp.send();
        }
    </script>
    <script>
        function populateAwayStatTable(club_id) {
            document.getElementById("substitution-table-away").innerHTML = "";
            document.getElementById("add-sub-away-button").disabled = false;
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("stat-table-away").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "ajax-inputstats.php?club_id=" + club_id, true);
            xmlhttp.send();
        }
    </script>
    <script>
        function toggleStatRow(row_id, enable) {
            var row = document.getElementById(row_id);
            for (var child of row.children) {
                if (child.className === 'toggleable') {
                    if (enable) {
                        child.children[0].disabled = false;
                    } else {
                        child.children[0].disabled = true;
                        child.children[0].value = "";
                    }
                }
            }

        }
    </script>
    <script>
        function clearInputs() {
            document.getElementById("week-number").value="";
            document.getElementById("home-club-select").value="";
            document.getElementById("home-possession").value="";
            document.getElementById("away-club-select").value="";
            document.getElementById("away-possession").value="";
            document.getElementById("substitution-table-home").innerHTML = "";
            document.getElementById("substitution-table-away").innerHTML = "";
            document.getElementById("stat-table-home").innerHTML = "";
            document.getElementById("stat-table-away").innerHTML = "";
            document.getElementById("add-sub-home-button").disabled = true;
            document.getElementById("add-sub-away-button").disabled = true;

        }
    </script>
    <script>
        function addSubstitutionHome() {
            sub_table = document.getElementById("substitution-table-home");
            club_id = document.getElementById("home-club-select").value;
            next_id = sub_table.children.length;
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("substitution-table-home").innerHTML += this.responseText;
                }
            };
            xmlhttp.open("GET", "ajax-addsubrow.php?next_id=" + next_id + "&club_id=" + club_id + "&is_home=" + true, true);
            xmlhttp.send();
        }
    </script>
    <script>
        function addSubstitutionAway() {
            sub_table = document.getElementById("substitution-table-away");
            club_id = document.getElementById("away-club-select").value;
            next_id = sub_table.children.length;
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("substitution-table-away").innerHTML += this.responseText;
                }
            };
            xmlhttp.open("GET", "ajax-addsubrow.php?next_id=" + next_id + "&club_id=" + club_id + "&is_home=" + false, true);
            xmlhttp.send();
        }
    </script>
</head>
<body onload="clearInputs()">
<h2>Add a fixture</h2>
<form action="add-fixture-doit.php" method="post">
    <table>
        <tr>
            <td>Week number: </td>
            <td><input id="week-number" name="week_number" type="number" min="1"/></td>
        </tr>
        <tr>
            <td>Home team: </td>
            <td>
                <select id="home-club-select" name="home_club_id" onchange="populateHomeStatTable(this.value)">
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
                        echo '<option value="" selected disabled hidden>Select a team</option>';
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
            </td>
            <td>Possession:</td>
            <td>
                <input id="home-possession" name="home_possession" type="number" min="0" max="100" onchange="calculateAwayPossession(this.value)"/>
            </td>
        </tr>
        <tr>
            <td>Away team: </td>
            <td>
                <select id="away-club-select" name="away_club_id" onchange="populateAwayStatTable(this.value)">
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
                        echo '<option value="" selected disabled hidden>Select a team</option>';
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
            </td>
            <td>Possession:</td>
            <td>
                <input id="away-possession" type="number" disabled/>
            </td>
        </tr>
    </table>
    <h2>Substitutions</h2>
    <h3>Home substitutions</h3>
    <table id="substitution-table-home">

    </table>
    <button type="button" id="add-sub-home-button" onclick="addSubstitutionHome()" disabled>+ substitution</button>

    <h3>Away substitutions</h3>
    <table id="substitution-table-away">

    </table>
    <button type="button" id="add-sub-away-button" onclick="addSubstitutionAway()" disabled>+ substitution</button>

    <h2>Stats</h2>
    <h3>Home stats</h3>
    <table id="stat-table-home">


    </table>
    <h3>Away stats</h3>
    <table id="stat-table-away">


    </table>
    <button id="add" type="submit">Add</button>
    <button id="clear" type="button" onclick="clearInputs()">Clear</button>
</form>

</body>
</html>