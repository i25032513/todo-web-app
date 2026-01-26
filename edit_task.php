<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: view_tasks.php");
    exit();
}

$task = $result->fetch_assoc();
$stmt->close();

$success = "";
$error = "";

if (isset($_POST['update'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, category = ?, priority = ?, due_date = ?, status = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssssii", $title, $description, $category, $priority, $due_date, $status, $task_id, $user_id);

    if ($stmt->execute()) {
        $success = "Task updated successfully!";
        $task['title'] = $title;
        $task['description'] = $description;
        $task['category'] = $category;
        $task['priority'] = $priority;
        $task['due_date'] = $due_date;
        $task['status'] = $status;
    } else {
        $error = "Failed to update task.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ToDo Student | Edit Task</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="main">
            <div class="header">
                <h3>Edit Task</h3>
            </div>

            <div class="card" style="max-width: 500px;">
                <?php if ($success): ?>
                    <p style="color: green; margin-bottom: 15px;">
                        <?php echo $success; ?>
                    </p>
                <?php endif; ?>
                <?php if ($error): ?>
                    <p style="color: red; margin-bottom: 15px;">
                        <?php echo $error; ?>
                    </p>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required
                            maxlength="200">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description"
                            rows="4"><?php echo htmlspecialchars($task['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="Assignment" <?php echo $task['category'] == 'Assignment' ? 'selected' : ''; ?>
                                >Assignment</option>
                            <option value="Discussion" <?php echo $task['category'] == 'Discussion' ? 'selected' : ''; ?>
                                >Discussion</option>
                            <option value="Club Activity" <?php echo $task['category'] == 'Club Activity' ? 'selected' : ''; ?>>Club Activity</option>
                            <option value="Examination" <?php echo $task['category'] == 'Examination' ? 'selected' : ''; ?>>Examination</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" required>
                            <option value="Low" <?php echo ($task['priority'] ?? '') == 'Low' ? 'selected' : ''; ?>>Low
                            </option>
                            <option value="Medium" <?php echo ($task['priority'] ?? 'Medium') == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="High" <?php echo ($task['priority'] ?? '') == 'High' ? 'selected' : ''; ?>
                                >High</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" value="<?php echo $task['due_date']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="Pending" <?php echo $task['status'] == 'Pending' ? 'selected' : ''; ?>>Pending
                            </option>
                            <option value="On-going" <?php echo $task['status'] == 'On-going' ? 'selected' : ''; ?>
                                >On-going</option>
                            <option value="Completed" <?php echo $task['status'] == 'Completed' ? 'selected' : ''; ?>
                                >Completed</option>
                        </select>
                    </div>

                    <button type="submit" name="update" class="btn">Update Task</button>
                    <a href="view_tasks.php" class="btn" style="background: #666; margin-left: 10px;">Cancel</a>
                </form>
            </div>
        </div>
    </div>

</body>

</html>