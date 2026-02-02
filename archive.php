<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle restore action
if (isset($_GET['restore'])) {
    $task_id = (int) $_GET['restore'];
    $stmt = $conn->prepare("UPDATE tasks SET is_archived = 0 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    if ($stmt->execute()) {
        $_SESSION['archive_message'] = 'Task restored successfully!';
        $_SESSION['message_type'] = 'success';
    }
    $stmt->close();
    header("Location: archive.php");
    exit();
}

// Get archive message if exists
$archive_message = $_SESSION['archive_message'] ?? '';
$message_type = $_SESSION['message_type'] ?? 'success';
unset($_SESSION['archive_message'], $_SESSION['message_type']);

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
        .archive-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 16px;
            color: white;
            box-shadow: var(--shadow-md);
        }

        .archive-header h3 {
            color: white;
            margin: 0;
            font-size: 28px;
        }

        .archive-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
        }

        .sticky-actions {
            margin-top: 15px;
            display: flex;
            gap: 8px;
        }

        .sticky-actions a,
        .sticky-actions button {
            flex: 1;
            font-size: 12px;
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .restore-btn {
            background: var(--success-color);
            color: white;
        }

        .restore-btn:hover {
            background: #25a89a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(46, 196, 182, 0.3);
        }

        .delete-btn {
            background: var(--danger-color);
            color: white;
        }

        .delete-btn:hover {
            background: #c41828;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 29, 54, 0.3);
        }

        .archived-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.2);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .empty-archive {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
        }

        .empty-archive-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-archive h4 {
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .empty-archive p {
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .flash-message {
            background: var(--success-color);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-md);
            animation: slideDown 0.3s ease;
        }

        .flash-message.error {
            background: var(--danger-color);
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .flash-close {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="main">
            <?php if ($archive_message): ?>
                <div class="flash-message <?php echo $message_type; ?>" id="flashMessage">
                    <span>✓ <?php echo htmlspecialchars($archive_message); ?></span>
                    <button class="flash-close" onclick="closeFlash()">×</button>
                </div>
            <?php endif; ?>

            <div class="archive-header">
                <h3>📦 Archived Tasks</h3>
                <div class="archive-count"><?php echo $tasks->num_rows; ?> Tasks</div>
            </div>

            <?php if ($tasks->num_rows > 0): ?>
                <div class="sticky-container">
                    <?php while ($task = $tasks->fetch_assoc()): ?>
                        <div class="sticky <?php echo $colors[$task['category']] ?? 'yellow'; ?>" style="opacity: 0.8; position: relative;">
                            <span class="archived-badge">Archived</span>
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
                <div class="empty-archive">
                    <div class="empty-archive-icon">📦</div>
                    <h4>No Archived Tasks</h4>
                    <p>Completed tasks will appear here when you archive them.</p>
                    <a href="view_tasks.php" class="btn">View My Tasks</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function closeFlash() {
            const flash = document.getElementById('flashMessage');
            if (flash) {
                flash.style.transition = 'opacity 0.3s, transform 0.3s';
                flash.style.opacity = '0';
                flash.style.transform = 'translateY(-20px)';
                setTimeout(() => flash.remove(), 300);
            }
        }

        // Auto-hide flash message after 5 seconds
        const flashMessage = document.getElementById('flashMessage');
        if (flashMessage) {
            setTimeout(() => closeFlash(), 5000);
        }
    </script>

</body>

</html>