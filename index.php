<?php
session_start();
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE first_name = ? AND last_name = ?");
    $stmt->bind_param("ss", $first_name, $last_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
    } else {
        
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $first_name, $last_name);
        $stmt->execute();
        $_SESSION['user_id'] = $conn->insert_id; 
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
    }
    header("Location: quiz.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Login</title>
    <link rel="stylesheet" href="style.css">  
</head>
<body>
    <h1>Welcome to the Quiz</h1>

    
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required><br>
        <input type="text" name="last_name" placeholder="Last Name" required><br>
        <button type="submit" name="submit">Start Quiz</button>
    </form>

    <hr>

    <h2>Top 10 Leaderboard</h2>
    <?php
    // Fetch top 10 leaderboard based on highest scores
    $query = "SELECT u.first_name, u.last_name, q.score, q.time_taken 
              FROM quiz_results q 
              JOIN users u ON q.user_id = u.id 
              ORDER BY q.score DESC, q.time_taken ASC 
              LIMIT 10";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Name</th>
                    <th>Score</th>
                    <th>Time Taken</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['first_name']} {$row['last_name']}</td>
                    <td>{$row['score']}</td>
                    <td>{$row['time_taken']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No leaderboard data available.";
    }
    ?>
</body>
</html>
