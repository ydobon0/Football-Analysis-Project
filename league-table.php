<!doctype html>
<html>
<head>
    <title>League table</title>
    <style>
        th, td {
            border: 1px solid black;
        }
    </style>
</head>
<body>
<h2>League table</h2>
    <table>
        <tr>
            <th>Rank</th><th>Club</th><th>Wins</th><th>Draws</th><th>Losses</th><th>Played</th><th>GF</th><th>GA</th><th>GD</th><th>Points</th>
        </tr>
        <?php
        // apparently mysqli keeps losing my order so we gotta define our own php sorting function
        function row_compare($a, $b) {
            if ($b["points"] - $a["points"] != 0) {
                return $b["points"] - $a["points"];
            } else {
                return $b["GD"] - $a["GD"];
            }
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

        // Start transaction
        $conn->query("BEGIN TRANSACTION");

        /* this was a huge pain to write */
        // Clear temporary tables
        $sql = 'drop temporary table if exists club_goals_scored, club_goals_lost_home, club_goals_lost_away, club_GFGAGD, fixture_home_data, fixture_away_data, fixture_results_home, fixture_results_away, club_wlt;';
        if($conn->query($sql) == false) {
            echo "<p>Error deleting from database</p>";
            echo "<p>" . htmlspecialchars($conn->error) . "</p>";
            $conn->query("ROLLBACK;");
            $conn->close();
            return;
        }

        // Why does this league table need so many temp tables
        $sqls = array(
            "create temporary table club_goals_scored select club_id, sum(goals) as goals_scored from (select C.club_id, sum(FPS.goals) as goals from Club C, Player P, ClubPlayer CP, FixturePlayerStats FPS where C.club_id = CP.club_id and CP.player_id = P.player_id and FPS.player_id = P.player_id group by C.club_id union all select club_id, 0 from Club) all_goals group by club_id;",
            "create temporary table club_goals_lost_home select club_id, sum(goals) as goals_lost_home from (select Ch.club_id, sum(FPS.goals) as goals from Fixture F, Club Ch, Club Ca, ClubPlayer CPa, Player Pa, FixturePlayerStats FPS where Ch.club_id = F.home_club_id and Ca.club_id = F.away_club_id and CPa.club_id = Ca.club_id and Pa.player_id = CPa.player_id and FPS.player_id = CPa.player_id and FPS.fixture_id = F.fixture_id group by Ch.club_id union all select club_id, 0 from Club) goals_all group by club_id;",
            "create temporary table club_goals_lost_away select club_id, sum(goals) as goals_lost_away from (select Ca.club_id, sum(FPS.goals) as goals from Fixture F, Club Ch, Club Ca, ClubPlayer CPh, Player Ph, FixturePlayerStats FPS where Ch.club_id = F.home_club_id and Ca.club_id = F.away_club_id and CPh.club_id = Ch.club_id and Ph.player_id = CPh.player_id and FPS.player_id = CPh.player_id and FPS.fixture_id = F.fixture_id group by Ca.club_id union all select club_id, 0 from Club) goals_all group by club_id;",
            "create temporary table club_GFGAGD select s.club_id, goals_scored as GF, goals_lost_home + goals_lost_away as GA, goals_scored - goals_lost_away - goals_lost_home as GD from club_goals_scored s, club_goals_lost_home lh, club_goals_lost_away la where s.club_id = lh.club_id and lh.club_id = la.club_id;",
            "CREATE TEMPORARY TABLE fixture_home_data SELECT F.fixture_id, F.week_number, CP.club_id as home_team_id, sum(FPS.goals) as home_goals FROM Fixture F, FixturePlayerStats FPS, ClubPlayer CP WHERE F.fixture_id = FPS.fixture_id and CP.player_id = FPS.player_id and CP.club_id = F.home_club_id GROUP BY fixture_id;",
            "CREATE TEMPORARY TABLE fixture_away_data SELECT F.fixture_id, CP.club_id as away_team_id, sum(FPS.goals) as away_goals FROM Fixture F, FixturePlayerStats FPS, ClubPlayer CP WHERE F.fixture_id = FPS.fixture_id and CP.player_id = FPS.player_id and CP.club_id = F.away_club_id GROUP BY fixture_id;",
            "create temporary table fixture_results_home select home_team_id, sum(home_wins) as home_wins, sum(away_wins) as away_wins, sum(ties) as ties, sum(points_home) as points_home from (select home_team_id, sum(home_goals > away_goals) as home_wins, sum(home_goals < away_goals) as away_wins, sum(home_goals = away_goals) as ties, sum(home_goals > away_goals) * 3 + sum(home_goals = away_goals) as points_home from fixture_home_data h, fixture_away_data a where h.fixture_id = a.fixture_id group by home_team_id union all select club_id, 0, 0, 0, 0 from Club) results_home group by home_team_id;",
            "create temporary table fixture_results_away select away_team_id, sum(home_wins) as home_wins, sum(away_wins) as away_wins, sum(ties) as ties, sum(points_away) as points_away from (select away_team_id, sum(home_goals > away_goals) as home_wins, sum(home_goals < away_goals) as away_wins, sum(home_goals = away_goals) as ties, sum(home_goals < away_goals) * 3 + sum(home_goals = away_goals) as points_away from fixture_home_data h, fixture_away_data a where h.fixture_id = a.fixture_id group by away_team_id union all select club_id, 0, 0, 0, 0 from Club) results_home group by away_team_id;",
            "create temporary table club_wlt select h.home_team_id as club_id, h.home_wins + h.away_wins + h.ties + a.home_wins + a.away_wins + a.ties as total_played, h.home_wins + a.away_wins as total_wins, h.away_wins + a.home_wins as total_loss, h.ties + a.ties as total_ties, points_home + points_away as points_total from fixture_results_home h, fixture_results_away a where h.home_team_id = a.away_team_id;"
        );
        foreach ($sqls as $sql) {
            if($conn->query($sql) == false) {
                echo "<p>Error creating temporary data in database</p>";
                echo "<p>" . htmlspecialchars($conn->error) . "</p>";
                $conn->query("ROLLBACK;");
                $conn->close();
                return;
            }
        }

        // Final query
        $sql = "select C.name, total_wins as wins, total_ties as draws, total_loss as losses, total_played as played, GF, GA, GD, points_total as points from Club C, club_wlt wlt, club_GFGAGD GDGAGD where C.club_id = wlt.club_id and C.club_id = GDGAGD.club_id order by points, GD desc;";
        $result = $conn->query($sql);
        if ($result == false) {
            echo "<p>Error getting data from database</p>";
            echo "<p>" . htmlspecialchars($conn->error) . "</p>";
            $conn->query("ROLLBACK;");
            $conn->close();
            return;
        } else {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            uasort($rows, 'row_compare');
            $i = 1;
            foreach ($rows as $row) {
                echo sprintf("<tr><td>%d</td><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td></tr>",
                    $i, $row["name"], $row["wins"], $row["ties"], $row["losses"], $row["played"], $row["GF"], $row["GA"], $row["GD"], $row["points"]);
                $i++;
            }
        }

        // Clear temporary tables
        $sql = 'drop temporary table if exists club_goals_scored, club_goals_lost_home, club_goals_lost_away, club_GFGAGD, fixture_home_data, fixture_away_data, fixture_results_home, fixture_results_away, club_wlt;';
        if($conn->query($sql) == false) {
            echo "<p>Error deleting from database</p>";
            echo "<p>" . htmlspecialchars($conn->error) . "</p>";
            $conn->query("ROLLBACK;");
            $conn->close();
            return;
        }

        // Commit
        $sql->query("COMMIT;");

        // Close connection
        $conn->close();
        ?>
    </table>
</body>
</html>