<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$priorities = ['High', 'Medium', 'Low'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Priority View</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .priority-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .priority-header h3 {
            margin: 0;
            font-size: 26px;
            color: var(--text-color);
        }

        .priority-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 4px;
        }

        .priority-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 18px;
        }

        .priority-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 18px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .priority-card h4 {
            margin: 0 0 12px 0;
            font-size: 16px;
            color: var(--text-color);
        }

        .priority-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .priority-badge.high { background: #ffebee; color: #c62828; }
        .priority-badge.medium { background: #fff3e0; color: #ef6c00; }
        .priority-badge.low { background: #e8f5e9; color: #2e7d32; }

        .task-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 12px;
        }

        .task-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            background: var(--light-bg);
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        .task-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .task-status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .task-status.pending { background: #fff3e0; color: #f57c00; }
        .task-status.ongoing { background: #e3f2fd; color: #1976d2; }
        .task-status.completed { background: #e8f5e9; color: #2e7d32; }

        .empty-state {
            text-align: center;
            color: var(--text-muted);
            padding: 16px 10px;
            font-size: 13px;
        }

        body.dark-mode .priority-card {
            background: #1e1e1e;
            border-color: #333;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        }

        body.dark-mode .task-row {
            background: #2a2a2a;
            border-color: #333;
        }

        body.dark-mode .task-title,
        body.dark-mode .priority-header h3,
        body.dark-mode .priority-card h4 {
            color: #e0e0e0;
        }

        body.dark-mode .priority-subtitle,
        body.dark-mode .empty-state {
            color: #cbd5e1;
        }
    </style>
</head>
<body>
<div class="wrapper">
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="priority-header">
        <div>
            <h3>🎯 Tasks by Priority</h3>
            <div class="priority-subtitle">Organize tasks based on priority level</div>
        </div>
    </div>

    <div class="priority-grid">
        <?php foreach ($priorities as $p): 
            $stmt = $conn->prepare("
                SELECT * FROM tasks
                WHERE user_id = ? AND priority = ? AND is_archived = 0
            ");
            $stmt->bind_param("is", $user_id, $p);
            $stmt->execute();
            $tasks = $stmt->get_result();
            $priorityClass = strtolower($p);
        ?>
            <div class="priority-card">
                <div class="priority-badge <?php echo $priorityClass; ?>">
                    <?php echo $p; ?> Priority
                </div>
                <?php if ($tasks->num_rows > 0): ?>
                    <div class="task-list">
                        <?php while ($t = $tasks->fetch_assoc()): ?>
                            <?php $statusClass = strtolower(str_replace('-', '', $t['status'])); ?>
                            <div class="task-row">
                                <p class="task-title"><?php echo htmlspecialchars($t['title']); ?></p>
                                <span class="task-status <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($t['status']); ?>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">No tasks found.</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</div>
</body>
</html>
