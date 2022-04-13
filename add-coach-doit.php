<!DOCTYPE html>
<html>
<head>
    <title>Add a coach</title>
</head>
<body>
    <?php
    // Get variables from request
    $club_id = $_POST["club_id"];
    $fname = $_POST["fname"];
    $lname = $_POST["lname"];

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
    }
    if ($invalid) {
        $conn->close();
        return;
    }

    // Start a transaction
    $sql = "START TRANSACTION;";
    $conn->query($sql);

    // Insert new coach into Manager
    $sql = 'INSERT INTO Manager (fname, lname, last_club_id) VALUES ("' . $fname . '", "' . $lname . '", ' . $club_id . ');';
    if($conn->query($sql) == false) {
        echo "<p>Error adding coach to database</p>";
        echo "<p>" . htmlspecialchars($conn->error) . "</p>";
        $conn->query("ROLLBACK;");
        $conn->close();
        return;
    }

    // Get new coach id
    $coach_id = $conn->insert_id;

    // Remove for existing coach(es)
    $sql = "SELECT manager_id FROM ClubManager WHERE club_id = " . $club_id . ";";
    $existing_coaches = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($existing_coaches as $existing_coach) {
        $sql = "DELETE FROM ClubManager WHERE club_id = " . $club_id . " AND manager_id = " . $existing_coach["manager_id"] . ";";
        if($conn->query($sql) == false) {
            echo "<p>Error removing existing coaches</p>";
            echo "<p>" . htmlspecialchars($conn->error) . "</p>";
            $conn->query("ROLLBACK;");
            $conn->close();
            return;
        }
    }

    // Register coach with team
    $sql = "INSERT INTO ClubManager VALUES (" . $coach_id . ", ". $club_id . ");";
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
            window.location='add-coach.php'
        }, 3000);
    </script>
</body>
</html>