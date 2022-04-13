<!doctype html>
<html>
<head>
    <title>Add player</title>
    <script>
        function getFreeKitNums(club_id) {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("number-select").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "ajax-freekitnums.php?club_id=" + club_id, true);
            xmlhttp.send();
        }
    </script>
    <script>
        function clearInputs() {
            document.getElementById("club-select").value="";
            document.getElementById("fname").value="";
            document.getElementById("lname").value="";
            document.getElementById("number-select").innerHTML = '<option value="" selected disabled hidden></option>';
            document.getElementById("number-select").value="";
        }
    </script>
</head>
<body onload="clearInputs()">
<h2>Add a player</h2>
<form action="add-player-doit.php" method="post">
    <table>
        <tr>
            <td>Select a team</td>
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
                    echo '<select name="club_id" id="club-select" onchange="getFreeKitNums(this.value)">';
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
            <td>First name</td>
            <td><input id="fname" name="fname" type="text"/></td>
        </tr>
        <tr>
            <td>Last name</td>
            <td><input id="lname" name="lname" type="text"/></td>
        </tr>
        <tr>
            <td>Number</td>
            <td>
                <select id="number-select" name="number">
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