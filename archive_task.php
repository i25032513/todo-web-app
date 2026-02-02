<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($task_id > 0) {
    $stmt = $conn->prepare("UPDATE tasks SET is_archived = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    if ($stmt->execute()) {
        $_SESSION['task_success'] = 'Task archived successfully! You can restore it from the Archive page.';
    }
    $stmt->close();
}

header("Location: view_tasks.php");
exit();
?>