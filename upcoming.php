<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$today = date('Y-m-d');
$week = date('Y-m-d', strtotime('+7 days'));

$stmt = $conn->prepare("
    SELECT * FROM tasks
    WHERE user_id = ?
      AND is_archived = 0
      AND status != 'Completed'
      AND due_date BETWEEN ? AND ?
    ORDER BY due_date ASC
");
$stmt->bind_param("iss", $user_id, $today, $week);
$stmt->execute();
$tasks = $stmt->get_result();

function getDueBadge($due_date) {
    $today = new DateTime(date('Y-m-d'));
    $due = new DateTime($due_date);
    $diff = (int)$today->diff($due)->format('%r%a');

    if ($diff < 0) return ['overdue', 'Overdue'];
    if ($diff === 0) return ['today', 'Due Today'];
    if ($diff <= 3) return ['soon', 'Due Soon'];
    return ['normal', 'Upcoming'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upcoming Tasks</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .upcoming-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .upcoming-header h3 {
            margin: 0;
            font-size: 26px;
            color: var(--text-color);
        }

        .upcoming-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 4px;
        }

        .upcoming-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .task-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 16px;
            background: var(--light-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .task-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }

        .task-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .task-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge.priority-high { background: #ffebee; color: #c62828; }
        .badge.priority-medium { background: #fff3e0; color: #ef6c00; }
        .badge.priority-low { background: #e8f5e9; color: #2e7d32; }

        .badge.due-overdue { background: #ffebee; color: #c62828; }
        .badge.due-today { background: #fff3e0; color: #ef6c00; }
        .badge.due-soon { background: #e3f2fd; color: #1565c0; }
        .badge.due-normal { background: #f5f5f5; color: #607d8b; }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        body.dark-mode .upcoming-card {
            background: #1e1e1e;
            border-color: #333;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        }

        body.dark-mode .task-item {
            background: #2a2a2a;
            border-color: #333;
        }

        body.dark-mode .task-title,
        body.dark-mode .upcoming-header h3 {
            color: #e0e0e0;
        }

        body.dark-mode .task-meta,
        body.dark-mode .upcoming-subtitle {
            color: #cbd5e1;
        }
    </style>
</head>
<body>
<div class="wrapper">
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="upcoming-header">
        <div>
            <h3>📅 Upcoming Tasks</h3>
            <div class="upcoming-subtitle">Next 7 days overview</div>
        </div>
    </div>

    <div class="upcoming-card">
        <?php if ($tasks->num_rows > 0): ?>
            <div class="task-list">
                <?php while ($t = $tasks->fetch_assoc()): ?>
                    <?php [$dueClass, $dueLabel] = getDueBadge($t['due_date']); ?>
                    <div class="task-item">
                        <div class="task-info">
                            <h4 class="task-title"><?php echo htmlspecialchars($t['title']); ?></h4>
                            <div class="task-meta">
                                <span>Due: <?php echo date('M d, Y', strtotime($t['due_date'])); ?></span>
                                <span>Category: <?php echo htmlspecialchars($t['category']); ?></span>
                            </div>
                        </div>
                        <div class="task-meta">
                            <span class="badge priority-<?php echo strtolower($t['priority']); ?>">
                                <?php echo htmlspecialchars($t['priority']); ?>
                            </span>
                            <span class="badge due-<?php echo $dueClass; ?>">
                                <?php echo $dueLabel; ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">No upcoming tasks.</div>
        <?php endif; ?>
    </div>
</div>
</div>
</body>
</html>
