<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Hub</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.style.display = 'none';
            });
            document.getElementById(sectionId).style.display = 'block';

            // Update the current section in URL hash
            history.pushState(null, '', `#${sectionId}`);
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Show the section based on URL hash, default to 'home'
            const sectionId = location.hash ? location.hash.substring(1) : 'home';
            showSection(sectionId);

            // Prevent navigation links from scrolling the page
            const navLinks = document.querySelectorAll('nav a');
            navLinks.forEach(link => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                });
            });
        });
    </script>
</head>
<body>
    <header>
        <h1>Football Hub</h1>
    </header>
    <nav>
        <a href="#" onclick="showSection('home')">Leagues</a>
        <a href="#" onclick="showSection('teams')">Teams</a>
        <a href="#" onclick="showSection('games')">Games</a>
        <a href="#" onclick="showSection('players')">Players</a>
        <a href="#" onclick="showSection('favorites')">Favorites</a>
		<a href="#" onclick="showSection('appearances')">Appearances</a>
    </nav>

    
    <!-- Home Section -->
    <div id="home" class="section" style="display: none;">
        <h2>Football Leagues</h2>
        <p>Welcome to the Football Hub! Below are the current football leagues:</p>
        
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "1234";
        $dbname = "football";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Update leagueID if the form is submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_leagueID'], $_POST['leagueID'])) {
            $new_leagueID = $_POST['new_leagueID'];
            $leagueID = $_POST['leagueID'];

            // Update query to modify the leagueID
            $update_sql = "UPDATE leagues SET leagueID = ? WHERE leagueID = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param('ii', $new_leagueID, $leagueID);

            if ($stmt->execute()) {
                echo "<p>League ID updated successfully!</p>";
            } else {
                echo "<p>Error updating league ID: " . $conn->error . "</p>";
            }
            $stmt->close();
        }

        // Fetch all leagues
        $sql = "SELECT leagueID, name FROM leagues";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<table class="league-table">';
            echo '<thead><tr><th>League ID</th><th>League Name</th><th>Action</th></tr></thead>';
            echo '<tbody>';
            while ($row = $result->fetch_assoc()) {
                $leagueID = htmlspecialchars($row["leagueID"]);
                $leagueName = htmlspecialchars($row["name"]);
                
                // Display each league in a table row with an edit form button
                echo "<tr><td>$leagueID</td><td>$leagueName</td><td>
                        <form action='' method='POST'>
                            <input type='hidden' name='leagueID' value='$leagueID'>
                            <input type='number' name='new_leagueID' value='$leagueID' required>
                            <button type='submit'>Edit League ID</button>
                        </form>
                      </td></tr>";
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo "<p>No leagues found.</p>";
        }

        $conn->close();
        ?>
    </div>
</div>


        <!-- Teams Section -->
        <div id="teams" class="section" style="display: none;">
            <h2>Football Teams</h2>
            <p>Explore the teams playing in the leagues!</p>

            <?php
            // Reconnecting to the database
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // SQL query to fetch team details along with the league name
            $sql = "
                SELECT t.teamID, t.name AS team_name, l.name AS league_name
                FROM teams t
                JOIN leagues l ON t.leagueID = l.leagueID
            ";

            $result = $conn->query($sql);

            // Check if any teams exist
            if ($result->num_rows > 0) {
                echo '<table class="team-table">';
                echo '<thead><tr><th>Team ID</th><th>Team Name</th><th>League Name</th></tr></thead>';
                echo '<tbody>';
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>" . htmlspecialchars($row["teamID"]) . "</td><td>" . htmlspecialchars($row["team_name"]) . "</td><td>" . htmlspecialchars($row["league_name"]) . "</td></tr>";
                }
                echo '</tbody>';
                echo '</table>';
            } else {
                echo "<p>No teams found.</p>";
            }

            // Close the connection
            $conn->close();
            ?>
        </div>



       <!-- Games Section -->
    <div id="games" class="section" style="display: none;">
    <h2>Football Games</h2>
    <p>Explore the details of upcoming football games or add a new match:</p>

    <?php
    // Database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Queries to fetch data for dropdowns
    $leaguesQuery = "SELECT leagueID, name FROM leagues";
    $leaguesResult = $conn->query($leaguesQuery);

    $teamsQuery = "SELECT teamID, name FROM teams";
    $teamsResult = $conn->query($teamsQuery);

    $seasonsQuery = "SELECT DISTINCT season FROM games";
    $seasonsResult = $conn->query($seasonsQuery);

    // Capture user input from GET request
    $selectedLeague = isset($_GET['league']) ? $_GET['league'] : '';
    $selectedSeason = isset($_GET['season']) ? $_GET['season'] : '';
    $selectedGameID = isset($_GET['gameID']) ? $_GET['gameID'] : '';
    $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'ASC'; // Default to ASC
    ?>

    <!-- Filter Form -->
    <form method="GET" action="?#games">
        <label for="gameID">Game ID:</label>
        <input type="text" name="gameID" id="gameID" placeholder="Enter Game ID" value="<?php echo htmlspecialchars($selectedGameID); ?>">

        <label for="league">League:</label>
        <select name="league" id="league">
            <option value="">All Leagues</option>
            <?php
            while ($league = $leaguesResult->fetch_assoc()) {
                $isSelected = ($league['leagueID'] == $selectedLeague) ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($league['leagueID']) . "' $isSelected>" . htmlspecialchars($league['name']) . "</option>";
            }
            ?>
        </select>

        <label for="season">Season:</label>
        <select name="season" id="season">
            <option value="">All Seasons</option>
            <?php
            while ($season = $seasonsResult->fetch_assoc()) {
                $isSelected = ($season['season'] == $selectedSeason) ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($season['season']) . "' $isSelected>" . htmlspecialchars($season['season']) . "</option>";
            }
            ?>
        </select>

        <label for="sortOrder">Sort By Game ID:</label>
        <select name="sortOrder" id="sortOrder">
            <option value="ASC" <?php echo ($sortOrder == 'ASC') ? 'selected' : ''; ?>>Ascending</option>
            <option value="DESC" <?php echo ($sortOrder == 'DESC') ? 'selected' : ''; ?>>Descending</option>
        </select>

        <button type="submit">Filter</button>
    </form>

    <!-- Add Match Form -->
    <h3>Add a New Match</h3>
    <form method="POST" action="add_match.php">
        <label for="addLeague">League:</label>
        <select name="leagueID" id="addLeague" required>
            <option value="">Select League</option>
            <?php
            $leaguesResult = $conn->query($leaguesQuery); // Re-fetch leagues for the dropdown
            while ($league = $leaguesResult->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($league['leagueID']) . "'>" . htmlspecialchars($league['name']) . "</option>";
            }
            ?>
        </select>

        <label for="addSeason">Season:</label>
        <input type="text" name="season" id="addSeason" placeholder="e.g., 2023" required>

        <label for="homeTeam">Home Team:</label>
        <select name="homeTeamID" id="homeTeam" required>
            <option value="">Select Home Team</option>
            <?php
            while ($team = $teamsResult->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($team['teamID']) . "'>" . htmlspecialchars($team['name']) . "</option>";
            }
            ?>
        </select>

        <label for="awayTeam">Away Team:</label>
        <select name="awayTeamID" id="awayTeam" required>
            <option value="">Select Away Team</option>
            <?php
            $teamsResult = $conn->query($teamsQuery); // Re-fetch teams for the dropdown
            while ($team = $teamsResult->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($team['teamID']) . "'>" . htmlspecialchars($team['name']) . "</option>";
            }
            ?>
        </select>

        <label for="matchDate">Match Date:</label>
        <input type="date" name="date" id="matchDate" required>

        <label for="addHomeGoals">Home Goals:</label>
        <input type="text" name="homeGoals" id="addHomeGoals" required>
        <label for="addAwayGoals">Away Goals:</label>
        <input type="text" name="awayGoals" id="addAwayGoals" required>

        <button type="submit">Add Match</button>
    </form>

    <?php
    // Build the query with optional filters and sorting
    $sql = "SELECT g.gameID, l.name AS leagueName, g.season, g.date, 
                   g.leagueID, ht.name AS homeTeamName, 
                   at.name AS awayTeamName, 
                   g.homeGoals, g.awayGoals 
            FROM games g
            JOIN leagues l ON l.leagueID = g.leagueID
            JOIN teams ht ON ht.teamID = g.homeTeamID
            JOIN teams at ON at.teamID = g.awayTeamID";

    // Conditions for WHERE clause
    $conditions = [];
    if ($selectedGameID) {
        $conditions[] = "g.gameID = '" . $conn->real_escape_string($selectedGameID) . "'";
    }
    if ($selectedLeague) {
        $conditions[] = "g.leagueID = '" . $conn->real_escape_string($selectedLeague) . "'";
    }
    if ($selectedSeason) {
        $conditions[] = "g.season = '" . $conn->real_escape_string($selectedSeason) . "'";
    }

    // Append conditions if any
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    // Add sorting by gameID
    $sql .= " ORDER BY g.gameID " . ($sortOrder === 'DESC' ? 'DESC' : 'ASC');

    // Execute the query
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<table class="game-table">';
        echo '<thead><tr>
                  <th>Game ID</th>
                  <th>League ID</th>
                  <th>League</th>
                  <th>Season</th>
                  <th>Date</th>
                  <th>Home Team</th>
                  <th>Away Team</th>
                  <th>Home Goals</th>
                  <th>Away Goals</th>
                  <th>Actions</th>
              </tr></thead>';
        echo '<tbody>';
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                  <td>" . htmlspecialchars($row["gameID"]) . "</td>
                  <td>" . htmlspecialchars($row["leagueID"]) . "</td>
                  <td>" . htmlspecialchars($row["leagueName"]) . "</td>
                  <td>" . htmlspecialchars($row["season"]) . "</td>
                  <td>" . htmlspecialchars($row["date"]) . "</td>
                  <td>" . htmlspecialchars($row["homeTeamName"]) . "</td>
                  <td>" . htmlspecialchars($row["awayTeamName"]) . "</td>
                  <td>" . htmlspecialchars($row["homeGoals"]) . "</td>
                  <td>" . htmlspecialchars($row["awayGoals"]) . "</td>
                  <td><a href='remove_match.php?gameID=" . htmlspecialchars($row['gameID']) . "' onclick='return confirm(\"Are you sure you want to delete this match?\");'>Delete</a></td>
              </tr>";
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo "<p>No games found.</p>";
    }

    $conn->close();
    ?>
</div>



        <!-- Players Section -->
<div id="players" class="section" style="display: none;">
    <h2>Players</h2>
    <p>Explore the details of football players.</p>

    <?php
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if "favorites" table exists, create it if not
    $createFavoritesTable = "CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    playerID INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    added_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_player FOREIGN KEY (playerID) REFERENCES players(playerID)
    ) ENGINE=InnoDB";
    if (!$conn->query($createFavoritesTable)) {
        die("Error creating favorites table: " . $conn->error);
    }

    // Handle adding a player to the favorites table
    if (isset($_POST['add_to_favorites'])) {
        $playerID = $_POST['playerID'];
        $playerName = $_POST['playerName'];

        // Check if player is already in favorites
        $checkFavoriteQuery = $conn->prepare("SELECT * FROM favorites WHERE playerID = ?");
        $checkFavoriteQuery->bind_param("i", $playerID);
        $checkFavoriteQuery->execute();
        $result = $checkFavoriteQuery->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Player is already in favorites.');</script>";
        } else {
            // Insert into favorites
            $addFavoriteQuery = $conn->prepare("INSERT INTO favorites (playerID, name) VALUES (?, ?)");
            $addFavoriteQuery->bind_param("is", $playerID, $playerName);

            if ($addFavoriteQuery->execute()) {
                echo "<script>alert('Player added to favorites successfully.');</script>";
            } else {
                echo "<script>alert('Error adding player to favorites: " . $conn->error . "');</script>";
            }
        }
    }

    // Get filter values from the request
    $searchName = isset($_GET['name']) ? $_GET['name'] : '';
    $searchID = isset($_GET['playerID']) ? $_GET['playerID'] : '';

    // Build the SQL query with filters
    $sql = "SELECT playerID, name FROM players WHERE 1=1";
    if (!empty($searchName)) {
        $sql .= " AND name LIKE '%" . $conn->real_escape_string($searchName) . "%'";
    }
    if (!empty($searchID)) {
        $sql .= " AND playerID = '" . $conn->real_escape_string($searchID) . "'";
    }

    $result = $conn->query($sql);
    ?>

    <!-- Filter Form -->
    <form method="GET" action="#players">
        <label for="name">Player Name:</label>
        <input type="text" name="name" id="name" placeholder="Enter player name" value="<?php echo htmlspecialchars($searchName); ?>">

        <label for="playerID">Player ID:</label>
        <input type="text" name="playerID" id="playerID" placeholder="Enter player ID" value="<?php echo htmlspecialchars($searchID); ?>">

        <button type="submit">Filter</button>
    </form>

    <?php
    // Display results
    if ($result->num_rows > 0) {
        echo '<table class="player-table">';
        echo '<thead><tr><th>Player ID</th><th>Player Name</th><th>Action</th></tr></thead>';
        echo '<tbody>';
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["playerID"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
            echo "<td>";
            echo "<form method='POST' action='#players' style='display:inline;'>";
            echo "<input type='hidden' name='playerID' value='" . htmlspecialchars($row["playerID"]) . "'>";
            echo "<input type='hidden' name='playerName' value='" . htmlspecialchars($row["name"]) . "'>";
            echo "<button type='submit' name='add_to_favorites'>Add to Favorites</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo "<p>No players found.</p>";
    }

    $conn->close();
    ?>
</div>


<!-- Favorites Section -->
<div id="favorites" class="section" style="display: none;">
    <h2>Favorites</h2>
    <p>Explore the details of favorite football players.</p>

    <?php
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Handle remove request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_player_id'])) {
        $playerID = $conn->real_escape_string($_POST['remove_player_id']);
        $deleteSql = "DELETE FROM favorites WHERE playerID = '$playerID'";
        if ($conn->query($deleteSql) === TRUE) {
            echo "<p>Player with ID $playerID removed from favorites.</p>";
        } else {
            echo "<p>Error removing player: " . $conn->error . "</p>";
        }
    }

    // Query to fetch all players from favorites
    $sql = "SELECT playerID, name, added_time FROM favorites";
    $result = $conn->query($sql);

    // Display results
    if ($result->num_rows > 0) {
        echo '<table class="player-table">';
        echo '<thead><tr><th>Player ID</th><th>Player Name</th><th>Added Time</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["playerID"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["added_time"]) . "</td>";
            echo "<td>
                <form method='POST' style='display: inline;'>
                    <input type='hidden' name='remove_player_id' value='" . htmlspecialchars($row["playerID"]) . "'>
                    <button type='submit'>Remove</button>
                </form>
            </td>";
            echo "</tr>";
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo "<p>No players found in favorites.</p>";
    }

    $conn->close();
    ?>
</div>



	<!-- Appearances Section -->
<div id="appearances" class="section" style="display: none;">
    <h2>Player Appearances</h2>
    <p>Filter player appearances based on specific criteria:</p>

    <?php
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get filter values from the request (only if the form is submitted)
    $searchPlayerName = isset($_GET['playerName']) ? $_GET['playerName'] : '';
    $searchGameID = isset($_GET['gameID']) ? $_GET['gameID'] : '';
    $searchLeagueID = isset($_GET['leagueID']) ? $_GET['leagueID'] : '';
    $searchSeason = isset($_GET['season']) ? $_GET['season'] : '';

    // Display filter form
    ?>
    <!-- Filter Form -->
    <form method="GET" action="">
        <label for="playerName">Player Name:</label>
        <input type="text" name="playerName" id="playerName" value="<?php echo htmlspecialchars($searchPlayerName); ?>" placeholder="Enter Player Name">

        <label for="gameID">Game ID:</label>
        <input type="text" name="gameID" id="gameID" value="<?php echo htmlspecialchars($searchGameID); ?>" placeholder="Enter Game ID">

        <label for="leagueID">League ID:</label>
        <input type="text" name="leagueID" id="leagueID" value="<?php echo htmlspecialchars($searchLeagueID); ?>" placeholder="Enter League ID">

        <label for="season">Season:</label>
        <input type="text" name="season" id="season" value="<?php echo htmlspecialchars($searchSeason); ?>" placeholder="Enter Season">

        <button type="submit">Filter</button>
    </form>

    <?php
    // If any filter is applied, execute the query and show results
    if ($searchPlayerName || $searchGameID || $searchLeagueID || $searchSeason) {
        // Build the SQL query with filters
        $sql = "SELECT a.gameID, a.playerID, a.goals, a.ownGoals, a.shots, a.assists, a.keyPasses, 
                       a.position, a.yellowCard, a.redCard, a.time, a.leagueID, 
                       p.name AS playerName, 
                       g.date AS gameDate, 
					   g.season AS season,
                       l.name AS leagueName,
                       ht.name AS homeTeamName, at.name AS awayTeamName
                FROM appearances a
                JOIN players p ON a.playerID = p.playerID
                JOIN games g ON a.gameID = g.gameID
                JOIN leagues l ON a.leagueID = l.leagueID
                JOIN teams ht ON g.homeTeamID = ht.teamID
                JOIN teams at ON g.awayTeamID = at.teamID
                WHERE 1=1";

        // Add filter conditions to the SQL query
        if ($searchPlayerName) {
            $sql .= " AND p.name LIKE '%" . $conn->real_escape_string($searchPlayerName) . "%'";
        }
        if ($searchGameID) {
            $sql .= " AND a.gameID = '" . $conn->real_escape_string($searchGameID) . "'";
        }
        if ($searchLeagueID) {
            $sql .= " AND a.leagueID = '" . $conn->real_escape_string($searchLeagueID) . "'";
        }
        if ($searchSeason) {
            $sql .= " AND g.season = '" . $conn->real_escape_string($searchSeason) . "'";
        }

        // Optional: Pagination (Limit the number of results per page)
        $limit = 100; // Number of records per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        $sql .= " LIMIT $offset, $limit";

        $result = $conn->query($sql);

        // Display filtered results if available
        if ($result->num_rows > 0) {
            echo '<table class="appearance-table">';
            echo '<thead><tr>
                      <th>Game ID</th><th>Player Name</th><th>Goals</th><th>Own Goals</th><th>Shots</th>
                      <th>Assists</th><th>Key Passes</th><th>Position</th><th>Yellow Card</th><th>Red Card</th>
                      <th>Time Played</th><th>League</th><th>Home Team</th><th>Away Team</th><th>Game Date</th><th>Season</th>
                  </tr></thead>';
            echo '<tbody>';
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                          <td>" . htmlspecialchars($row['gameID']) . "</td>
                          <td>" . htmlspecialchars($row['playerName']) . "</td>
                          <td>" . htmlspecialchars($row['goals']) . "</td>
                          <td>" . htmlspecialchars($row['ownGoals']) . "</td>
                          <td>" . htmlspecialchars($row['shots']) . "</td>
                          <td>" . htmlspecialchars($row['assists']) . "</td>
                          <td>" . htmlspecialchars($row['keyPasses']) . "</td>
                          <td>" . htmlspecialchars($row['position']) . "</td>
                          <td>" . htmlspecialchars($row['yellowCard']) . "</td>
                          <td>" . htmlspecialchars($row['redCard']) . "</td>
                          <td>" . htmlspecialchars($row['time']) . "</td>
                          <td>" . htmlspecialchars($row['leagueName']) . "</td>
                          <td>" . htmlspecialchars($row['homeTeamName']) . "</td>
                          <td>" . htmlspecialchars($row['awayTeamName']) . "</td>
                          <td>" . htmlspecialchars($row['gameDate']) . "</td>
						  <td>" . htmlspecialchars($row['season']) . "</td>
                      </tr>";
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo "<p>No appearances found for the given filter criteria.</p>";
        }

        // Get total number of results to create pagination links
        $countResult = $conn->query("SELECT COUNT(*) AS total FROM appearances a
                                     JOIN players p ON a.playerID = p.playerID
                                     JOIN games g ON a.gameID = g.gameID
                                     JOIN leagues l ON a.leagueID = l.leagueID
                                     JOIN teams ht ON g.homeTeamID = ht.teamID
                                     JOIN teams at ON g.awayTeamID = at.teamID
                                     WHERE 1=1");

        $totalRows = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalRows / $limit);

        // Display pagination links
        if ($totalPages > 1) {
            echo '<div class="pagination">';
            // Show previous button if not on the first page
            if ($page > 1) {
                echo "<a href='?page=" . ($page - 1) . "&playerName=$searchPlayerName&gameID=$searchGameID&leagueID=$searchLeagueID&season=$searchSeason'>&laquo; Previous</a> ";
            }

            // Calculate the range of pages to display (show 10 pages at a time)
            $startPage = max(1, $page - 5); // Start page (no less than 1)
            $endPage = min($totalPages, $page + 5); // End page (no more than the total pages)

            for ($i = $startPage; $i <= $endPage; $i++) {
                echo "<a href='?page=$i&playerName=$searchPlayerName&gameID=$searchGameID&leagueID=$searchLeagueID&season=$searchSeason'>$i</a> ";
            }

            // Show next button if not on the last page
            if ($page < $totalPages) {
                echo "<a href='?page=" . ($page + 1) . "&playerName=$searchPlayerName&gameID=$searchGameID&leagueID=$searchLeagueID&season=$searchSeason'>Next &raquo;</a>";
            }

            echo '</div>';
        }
    }

    // Close the connection
    $conn->close();
    ?>
</div>

</body>
</html>