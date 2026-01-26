<?php
session_start();
include 'db/config.php';
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
}

if(isset($_POST['submit'])){
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $due_date = $_POST['due_date'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO tasks (user_id,title,description,category,due_date,status)
            VALUES ('$user_id','$title','$description','$category','$due_date','Pending')";
    if($conn->query($sql) === TRUE){
        $success = "Task added successfully!";
    } else {
        $error = $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Task</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Add Task</h2>
<?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post" action="">
    Title: <input type="text" name="title" required><br>
    Description: <textarea name="description" required></textarea><br>
    Category:
    <select name="category" required>
        <option value="Assignment">Assignment</option>
        <option value="Discussion">Discussion</option>
        <option value="Club Activity">Club Activity</option>
        <option value="Examination">Examination</option>
    </select><br>
    Due Date: <input type="date" name="due_date" required><br>
    <input type="submit" name="submit" value="Add Task">
</form>
</body>
</html>
