<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'due_date';

$sql = "SELECT * FROM tasks WHERE user_id = ? AND is_archived = 0";
$params = [$user_id];
$types = "i";

if ($category_filter) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
    $types .= "s";
}
if ($priority_filter) {
    $sql .= " AND priority = ?";
    $params[] = $priority_filter;
    $types .= "s";
}
if ($status_filter) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$valid_sorts = ['due_date', 'priority', 'created_at', 'title'];
if (in_array($sort_by, $valid_sorts)) {
    $sql .= " ORDER BY $sort_by ASC";
} else {
    $sql .= " ORDER BY due_date ASC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$tasks = $stmt->get_result();

$colors = ['Assignment' => 'yellow', 'Discussion' => 'blue', 'Club Activity' => 'green', 'Examination' => 'pink'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ToDo Student | View Tasks</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filters select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .sticky-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sticky-actions a,
        .sticky-actions button {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .sticky-actions a {
            background: #4a5d23;
            color: white;
        }

        .sticky-actions .delete-btn {
            background: #e74c3c;
            color: white;
        }

        .sticky-actions .archive-btn {
            background: #3498db;
            color: white;
        }

        .priority-badge {
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            background: #ddd;
        }

        .priority-badge.high {
            background: #e74c3c;
            color: white;
        }

        .priority-badge.medium {
            background: #f39c12;
            color: white;
        }

        .priority-badge.low {
            background: #2ecc71;
            color: white;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="main">
            <div class="header">
                <h3>My Tasks</h3>
                <a href="add_task.php" class="btn">+ Add Task</a>
            </div>

            <form method="get" class="filters">
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="Assignment" <?php echo $category_filter == 'Assignment' ? 'selected' : ''; ?>>
                        Assignment</option>
                    <option value="Discussion" <?php echo $category_filter == 'Discussion' ? 'selected' : ''; ?>>
                        Discussion</option>
                    <option value="Club Activity" <?php echo $category_filter == 'Club Activity' ? 'selected' : ''; ?>>
                        Club Activity</option>
                    <option value="Examination" <?php echo $category_filter == 'Examination' ? 'selected' : ''; ?>>
                        Examination</option>
                </select>
                <select name="priority">
                    <option value="">All Priorities</option>
                    <option value="High" <?php echo $priority_filter == 'High' ? 'selected' : ''; ?>>High</option>
                    <option value="Medium" <?php echo $priority_filter == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="Low" <?php echo $priority_filter == 'Low' ? 'selected' : ''; ?>>Low</option>
                </select>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="On-going" <?php echo $status_filter == 'On-going' ? 'selected' : ''; ?>>On-going
                    </option>
                    <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?>>Completed
                    </option>
                </select>
                <select name="sort">
                    <option value="due_date" <?php echo $sort_by == 'due_date' ? 'selected' : ''; ?>>Sort by Due Date
                    </option>
                    <option value="priority" <?php echo $sort_by == 'priority' ? 'selected' : ''; ?>>Sort by Priority
                    </option>
                    <option value="created_at" <?php echo $sort_by == 'created_at' ? 'selected' : ''; ?>>Sort by Created
                    </option>
                    <option value="title" <?php echo $sort_by == 'title' ? 'selected' : ''; ?>>Sort by Title</option>
                </select>
                <button type="submit" class="btn">Apply</button>
                <a href="view_tasks.php" style="color: #666; font-size: 14px;">Clear</a>
            </form>

            <?php if ($tasks->num_rows > 0): ?>
                <div class="sticky-container">
                    <?php while ($task = $tasks->fetch_assoc()): ?>
                        <div class="sticky <?php echo $colors[$task['category']] ?? 'yellow'; ?>">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                                <span class="priority-badge <?php echo strtolower($task['priority'] ?? 'medium'); ?>">
                                    <?php echo $task['priority'] ?? 'Medium'; ?>
                                </span>
                            </div>
                            <p style="font-size: 12px; margin: 8px 0; color: #555;">
                                <?php echo htmlspecialchars(substr($task['description'] ?? '', 0, 80)); ?>
                                <?php echo strlen($task['description'] ?? '') > 80 ? '...' : ''; ?>
                            </p>
                            <p style="font-size: 11px; color: #777;">
                                <strong>Due:</strong> <?php echo $task['due_date']; ?><br>
                                <strong>Category:</strong> <?php echo $task['category']; ?>
                            </p>
                            <span class="status <?php echo strtolower(str_replace('-', '', $task['status'])); ?>">
                                <?php echo $task['status']; ?>
                            </span>
                            <div class="sticky-actions">
                                <a href="edit_task.php?id=<?php echo $task['id']; ?>">Edit</a>
                                <form method="post" action="update_status.php" style="display: inline;">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <select name="new_status" onchange="this.form.submit()"
                                        style="font-size: 11px; padding: 4px;">
                                        <option value="">Change Status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="On-going">On-going</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </form>
                                <a href="archive_task.php?id=<?php echo $task['id']; ?>" class="archive-btn">Archive</a>
                                <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="delete-btn"
                                    onclick="return confirm('Delete this task?');">Delete</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <p>No tasks found. <a href="add_task.php">Add your first task</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>