<?php
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "football";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $leagueID = $_POST['leagueID'];
    $season = $_POST['season'];
    $homeTeamID = $_POST['homeTeamID'];
    $awayTeamID = $_POST['awayTeamID'];
    $date = $_POST['date'];

    $homeGoals = $_POST['homeGoals'];
    $awayGoals = $_POST['awayGoals'];

    $sql = "INSERT INTO games (leagueID, season, homeTeamID, awayTeamID, date, homeGoals, awayGoals) 
            VALUES ('$leagueID', '$season', '$homeTeamID', '$awayTeamID', '$date', '$homeGoals', '$awayGoals')";

    if ($conn->query($sql) === TRUE) {
        echo "New match added successfully!";
        header("Location: index.php#games");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
