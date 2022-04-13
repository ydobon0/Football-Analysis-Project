<!DOCTYPE html>
<html>
<head>
    <title>Add team</title>
</head>
<body>
<?php
// Get variables from request
$name = $_POST["name"];
$color1 = $_POST["color1"];
$color2 = $_POST["color2"];

// Verify all necessary variables are present
$insufficient = false;
if ($name === "") {
    echo "<p>Please enter a team name</p>";
    $insufficient = true;
}
if ($insufficient) {
    return;
}

// Transform x###### hex into integer
$color1 = hexdec(substr($color1, 1));
$color2 = hexdec(substr($color2, 1));

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

// Start a transaction
$sql = "START TRANSACTION;";
$conn->query($sql);

// Insert new club into Club
$sql = 'INSERT INTO Club (name, color1, color2) VALUES ("' . $name . '", ' . $color1 . ', ' . $color2 . ');';
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
        window.location='add-team.php'
    }, 3000);
</script>
</body>
</html>

