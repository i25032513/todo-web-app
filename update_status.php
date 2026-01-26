<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['task_id']) && isset($_POST['new_status'])) {
    $task_id = (int) $_POST['task_id'];
    $new_status = $_POST['new_status'];

    $valid_statuses = ['Pending', 'On-going', 'Completed'];

    if (in_array($new_status, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $new_status, $task_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: view_tasks.php");
exit();
?>