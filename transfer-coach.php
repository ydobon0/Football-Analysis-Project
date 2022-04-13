<!doctype html>
<html>
<head>
    <title>Transfer coach</title>
    <script>
        function getCoaches(club_id) {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("coach-select").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "ajax-teamcoaches.php?club_id=" + club_id, true);
            xmlhttp.send();
        }
    </script>
    <script>
        function clearInputs() {
            document.getElementById("old-club-select").value="";
            document.getElementById("new-club-select").value="";
            document.getElementById("coach-select").innerHTML = '<option value="" selected disabled hidden></option>';
            document.getElementById("coach-select").value="";
        }
    </script>
</head>
<body onload="clearInputs()">
<h2>Transfer a coach</h2>
<form action="transfer-coach-doit.php" method="post">
    <table>
        <tr>
            <td>Old team</td>
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
                    echo '<select name="old_club_id" id="old-club-select" onchange="getCoaches(this.value)">';
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
            <td>New team</td>
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
                    echo '<select name="new_club_id" id="new-club-select">';
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
            <td>Coach</td>
            <td>
                <select id="coach-select" name="manager_id">
                    <option value="" selected disabled hidden></option>
                </select>
            </td>
        </tr>
    </table>
    <button id="add" type="submit">Add</button>
    <button id="clear" type="button" onclick="clearInputs()">Clear</button>
</form>

</body>
</html>