<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['restore'])) {
    $task_id = (int) $_GET['restore'];
    $stmt = $conn->prepare("UPDATE tasks SET is_archived = 0 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: archive.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? AND is_archived = 1 ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tasks = $stmt->get_result();

$colors = ['Assignment' => 'yellow', 'Discussion' => 'blue', 'Club Activity' => 'green', 'Examination' => 'pink'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ToDo Student | Archive</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .sticky-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        .sticky-actions a {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
        }

        .restore-btn {
            background: #2ecc71;
            color: white;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="main">
            <div class="header">
                <h3>Archived Tasks</h3>
            </div>

            <?php if ($tasks->num_rows > 0): ?>
                <div class="sticky-container">
                    <?php while ($task = $tasks->fetch_assoc()): ?>
                        <div class="sticky <?php echo $colors[$task['category']] ?? 'yellow'; ?>" style="opacity: 0.8;">
                            <strong>
                                <?php echo htmlspecialchars($task['title']); ?>
                            </strong>
                            <p style="font-size: 12px; margin: 8px 0; color: #555;">
                                <?php echo htmlspecialchars(substr($task['description'] ?? '', 0, 80)); ?>
                            </p>
                            <p style="font-size: 11px; color: #777;">
                                <strong>Due:</strong>
                                <?php echo $task['due_date']; ?><br>
                                <strong>Category:</strong>
                                <?php echo $task['category']; ?>
                            </p>
                            <span class="status <?php echo strtolower(str_replace('-', '', $task['status'])); ?>">
                                <?php echo $task['status']; ?>
                            </span>
                            <div class="sticky-actions">
                                <a href="archive.php?restore=<?php echo $task['id']; ?>" class="restore-btn">Restore</a>
                                <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="delete-btn"
                                    onclick="return confirm('Permanently delete this task?');">Delete</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <p>No archived tasks. Completed tasks will appear here when archived.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>