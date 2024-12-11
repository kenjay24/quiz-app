<?php
session_start();
include 'conn.php';  

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); 
    exit;
}

$user_id = $_SESSION['user_id'];
$query = $conn->query("SELECT * FROM quiz_results WHERE user_id = $user_id");
if ($query->num_rows > 0) {
    echo "<h1 style='text-align:center;color:#e74c3c;'>You have already taken the quiz!</h1>";
    echo "<button style='display:block;margin:auto;padding:10px;background-color:#3498db;color:white;border:none;border-radius:5px;' onclick=\"window.location.href='index.php'\">Back to Index</button>";
    exit;
}

$questions = $conn->query("SELECT * FROM quiz_questions ORDER BY RAND() LIMIT 10");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['answers']) && is_array($_POST['answers'])) {
        $score = 0;

        
        $start_time = $_POST['start_time'];  
        $end_time = time();  
        $time_taken = $end_time - $start_time;  
        
        $formatted_time = gmdate("H:i:s", $time_taken);

        
        foreach ($_POST['answers'] as $question_id => $answer) {
            $query = $conn->query("SELECT correct_option FROM quiz_questions WHERE id = $question_id");
            $correct_answer = $query->fetch_assoc()['correct_option'];
            if ($answer == $correct_answer) {
                $score++;
            }
        }

        
        $stmt = $conn->prepare("INSERT INTO quiz_results (user_id, score, time_taken) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $score, $formatted_time);
        $stmt->execute();

        
        echo "<h1 style='text-align:center;color:#27ae60;'>Your Score: $score</h1>";
        echo "<h2 style='text-align:center;color:#3498db;'>Time Taken: $formatted_time</h2>";

        
        echo "<button style='display:block;margin:auto;padding:10px;background-color:#3498db;color:white;border:none;border-radius:5px;' onclick=\"window.location.href='index.php'\">Back to Index</button>";

        exit;
    } else {
        
        echo "<h1 style='text-align:center;color:#e74c3c;'>Please answer all questions before submitting!</h1>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <link rel="stylesheet" href="style.css"> 
    <script>
        
        function validateQuizForm() {
            var allAnswered = true;
            var questions = document.querySelectorAll('.question');
            questions.forEach(function (question) {
                var selectedAnswer = question.querySelector('input[type="radio"]:checked');
                if (!selectedAnswer) {
                    allAnswered = false;
                    question.style.border = '2px solid red';  
                } else {
                    question.style.border = '';  
                }
            });

            if (!allAnswered) {
                alert("Please answer all questions before submitting.");
            }

            return allAnswered;  
        }
    </script>
</head>
<body>
    <h1>Welcome to the Quiz, <?php echo $_SESSION['first_name']; ?>!</h1>

    
    <form method="POST" onsubmit="return validateQuizForm()">
        
        <input type="hidden" name="start_time" value="<?php echo time(); ?>">

        <?php while ($row = $questions->fetch_assoc()): ?>
    <div class="question">
        <p><?php echo $row['question_text']; ?></p>
        <input type="radio" name="answers[<?php echo $row['id']; ?>]" value="A" required> <?php echo $row['option_a']; ?><br>
        <input type="radio" name="answers[<?php echo $row['id']; ?>]" value="B" required> <?php echo $row['option_b']; ?><br>
        <input type="radio" name="answers[<?php echo $row['id']; ?>]" value="C" required> <?php echo $row['option_c']; ?><br>
        <input type="radio" name="answers[<?php echo $row['id']; ?>]" value="D" required> <?php echo $row['option_d']; ?><br>
    </div>
<?php endwhile; ?>


        <button type="submit">Submit Quiz</button>
    </form>
</body>
</html>
