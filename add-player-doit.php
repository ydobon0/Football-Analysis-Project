<!DOCTYPE html>
<html>
<head>
    <title>Add a player</title>
</head>
<body>
    <?php
    // Get variables from request
    $club_id = $_POST["club_id"];
    $fname = $_POST["fname"];
    $lname = $_POST["lname"];
    $number = $_POST["number"];

    // Verify all necessary variables are present
    $insufficient = false;
    if ($club_id === null) {
        echo "<p>Please choose a team</p>";
        $insufficient = true;
    }
    if ($fname === "") {
        echo "<p>Please enter a first name</p>";
        $insufficient = true;
    }
    if ($lname === "") {
        echo "<p>Please enter a last name</p>";
        $insufficient = true;
    }
    if ($number === null) {
        echo "<p>Please select a number</p>";
        $insufficient = true;
    }
    if ($insufficient) {
        return;
    }

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

    // Verify values of input
    $invalid = false;
    $sql = "SELECT club_id FROM Club WHERE club_id = " . $club_id . ";";
    $result = $conn->query($sql);
    if (count($result->fetch_all()) != 1) {
        echo "<p>Team does not exist in database</>";
        $invalid = true;
    } else {
        $sql = "SELECT kit_number FROM ClubPlayer WHERE club_id = " . $club_id . " AND kit_number = " . $number . ";";
        $result = $conn->query($sql);
        if (count($result->fetch_all()) != 0) {
            echo "<p>Number already in use</>";
            $invalid = true;
        }
    }
    if ($invalid) {
        $conn->close();
        return;
    }

    // Start a transaction
    $sql = "START TRANSACTION;";
    $conn->query($sql);

    // Insert new player into Player
    $sql = 'INSERT INTO Player (fname, lname) VALUES ("' . $fname . '", "' . $lname . '");';
    if($conn->query($sql) == false) {
        echo "<p>Error adding to database</p>";
        echo "<p>" . htmlspecialchars($conn->error) . "</p>";
        $conn->query("ROLLBACK;");
        $conn->close();
        return;
    }

    // Get new player id
    $player_id = $conn->insert_id;

    // Add register player with team
    $sql = "INSERT INTO ClubPlayer VALUES (" . $player_id . ", ". $club_id . ", " . $number .");";
    if($conn->query($sql) == false) {
        echo "<p>Error adding to database</p>";
        echo "<p>" . htmlspecialchars($conn->error) . "</p>";
        $conn->query("ROLLBACK;");
        $conn->close();
        return;
    }

    // Commit transaction
    $conn->query("COMMIT;");

    // Close connection
    $conn->close();

    echo "<p>Successfully added to database</p>"
    ?>
    <p>You will be redirected in 3 seconds</p>
    <script>
        var timer = setTimeout(function() {
            window.location='add-player.php'
        }, 3000);
    </script>
</body>
</html>