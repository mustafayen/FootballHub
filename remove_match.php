<?php
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "football";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['gameID'])) {
    $gameID = $_GET['gameID'];

    $sql = "DELETE FROM games WHERE gameID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $gameID);

    if ($stmt->execute()) {
        echo "Match deleted successfully!";
        header("Location: index.php#games");  
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
