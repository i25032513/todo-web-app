<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$total_tasks = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE user_id = $user_id AND is_archived = 0")->fetch_assoc()['count'];
$completed = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE user_id = $user_id AND status = 'Completed' AND is_archived = 0")->fetch_assoc()['count'];
$pending = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE user_id = $user_id AND status = 'Pending' AND is_archived = 0")->fetch_assoc()['count'];
$ongoing = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE user_id = $user_id AND status = 'On-going' AND is_archived = 0")->fetch_assoc()['count'];
$overdue = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE user_id = $user_id AND due_date < CURDATE() AND status != 'Completed' AND is_archived = 0")->fetch_assoc()['count'];

$recent_tasks = $conn->query("SELECT * FROM tasks WHERE user_id = $user_id AND is_archived = 0 ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ToDo Student | Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .stat-card h2 {
            font-size: 36px;
            margin-bottom: 5px;
            color: #4a5d23;
        }

        .stat-card p {
            color: #666;
            font-size: 14px;
        }

        .stat-card.overdue h2 {
            color: #e74c3c;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="main">
            <div class="header">
                <h3>Dashboard</h3>
                <a href="add_task.php" class="btn">+ Add Task</a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h2>
                        <?php echo $total_tasks; ?>
                    </h2>
                    <p>Total Tasks</p>
                </div>
                <div class="stat-card">
                    <h2>
                        <?php echo $ongoing; ?>
                    </h2>
                    <p>On-going</p>
                </div>
                <div class="stat-card">
                    <h2>
                        <?php echo $pending; ?>
                    </h2>
                    <p>Pending</p>
                </div>
                <div class="stat-card <?php echo $overdue > 0 ? 'overdue' : ''; ?>">
                    <h2>
                        <?php echo $overdue; ?>
                    </h2>
                    <p>Overdue</p>
                </div>
            </div>

            <div class="card">
                <h4 style="margin-bottom: 15px;">Recent Tasks</h4>
                <?php if ($recent_tasks->num_rows > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="text-align: left; border-bottom: 1px solid #eee;">
                            <th style="padding: 10px;">Title</th>
                            <th style="padding: 10px;">Category</th>
                            <th style="padding: 10px;">Due Date</th>
                            <th style="padding: 10px;">Status</th>
                            <th style="padding: 10px;">Actions</th>
                        </tr>
                        <?php while ($task = $recent_tasks->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px;">
                                    <?php echo htmlspecialchars($task['title']); ?>
                                </td>
                                <td style="padding: 10px;">
                                    <?php echo htmlspecialchars($task['category']); ?>
                                </td>
                                <td style="padding: 10px;">
                                    <?php echo $task['due_date']; ?>
                                </td>
                                <td style="padding: 10px;">
                                    <span class="status <?php echo strtolower(str_replace('-', '', $task['status'])); ?>">
                                        <?php echo $task['status']; ?>
                                    </span>
                                </td>
                                <td style="padding: 10px;">
                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" style="color: #4a5d23;">Edit</a> |
                                    <a href="view_tasks.php" style="color: #4a5d23;">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p>No tasks yet. <a href="add_task.php">Add your first task</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>