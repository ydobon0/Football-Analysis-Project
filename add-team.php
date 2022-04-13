<!doctype html>
<html>
<head>
    <title>Add team</title>
    <script>
        function clearInputs() {
            document.getElementById("name").value="";
            document.getElementById("color1").value="";
            document.getElementById("color2").value="";
        }
    </script>
</head>
<body onload="clearInputs()">
<h2>Add a team</h2>
<form action="add-team-doit.php" method="post">
    <table>
        <tr>
            <td>Team name</td>
            <td><input id="name" name="name" type="text"/></td>
        </tr>
        <tr>
            <td>Color 1</td>
            <td><input id="color1" name="color1" type="color"/></td>
        </tr>
        <tr>
            <td>Colour 2</td>
            <td>
                <input id="color2" name="color2" type="color"/>
            </td>
        </tr>
    </table>
    <button id="add" type="submit">Add</button>
    <button id="clear" type="button" onclick="clearInputs()">Clear</button>
</form>

</body>
</html>