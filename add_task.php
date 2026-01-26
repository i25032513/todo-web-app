<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, category, priority, due_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $title, $description, $category, $priority, $due_date, $status);

    if ($stmt->execute()) {
        $success = "Task added successfully!";
    } else {
        $error = "Failed to add task. Please try again.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ToDo Student | Add Task</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="main">
            <div class="header">
                <h3>Add New Task</h3>
            </div>

            <div class="card" style="max-width: 500px;">
                <?php if ($success): ?>
                    <p style="color: green; margin-bottom: 15px;"><?php echo $success; ?></p>
                <?php endif; ?>
                <?php if ($error): ?>
                    <p style="color: red; margin-bottom: 15px;"><?php echo $error; ?></p>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" required maxlength="200">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="Assignment">Assignment</option>
                            <option value="Discussion">Discussion</option>
                            <option value="Club Activity">Club Activity</option>
                            <option value="Examination">Examination</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" required>
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" required>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="Pending" selected>Pending</option>
                            <option value="On-going">On-going</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>

                    <button type="submit" name="submit" class="btn">Add Task</button>
                    <a href="view_tasks.php" class="btn" style="background: #666; margin-left: 10px;">Cancel</a>
                </form>
            </div>
        </div>
    </div>

</body>

</html>