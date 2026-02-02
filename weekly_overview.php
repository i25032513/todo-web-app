<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the start (Monday) and end (Sunday) of current week
$start = date('Y-m-d', strtotime('monday this week'));
$end = date('Y-m-d', strtotime('sunday this week'));

// Fetch all tasks for this week
$stmt = $conn->prepare("
    SELECT * FROM tasks
    WHERE user_id = ?
      AND due_date BETWEEN ? AND ?
      AND is_archived = 0
    ORDER BY due_date ASC, priority DESC
");
$stmt->bind_param("iss", $user_id, $start, $end);
$stmt->execute();
$result = $stmt->get_result();

// Organize tasks by day
$tasksByDay = [];
while ($task = $result->fetch_assoc()) {
    $day = date('Y-m-d', strtotime($task['due_date']));
    $tasksByDay[$day][] = $task;
}

// Get task statistics for the week
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'On-going' THEN 1 ELSE 0 END) as ongoing,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
    FROM tasks
    WHERE user_id = ?
      AND due_date BETWEEN ? AND ?
      AND is_archived = 0
");
$stmt->bind_param("iss", $user_id, $start, $end);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$colors = ['Assignment' => 'yellow', 'Discussion' => 'blue', 'Club Activity' => 'green', 'Examination' => 'pink'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Overview - ToDo Student</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .week-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-md);
        }

        .week-header h3 {
            margin: 0 0 10px 0;
            font-size: 28px;
            color: white;
        }

        .week-range {
            font-size: 14px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .week-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .week-stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 2px solid var(--border-color);
        }

        .week-stat-card h4 {
            font-size: 28px;
            margin: 0 0 5px 0;
            color: var(--primary-color);
        }

        .week-stat-card p {
            margin: 0;
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }

        .week-stat-card.completed h4 { color: var(--success-color); }
        .week-stat-card.ongoing h4 { color: var(--primary-color); }
        .week-stat-card.pending h4 { color: var(--warning-color); }

        .days-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .day-section {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--primary-color);
        }

        .day-section.today {
            border-left-color: var(--success-color);
            background: linear-gradient(to right, #f0fdf4, white);
        }

        .day-section.past {
            opacity: 0.7;
        }

        .day-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .day-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .day-badge {
            background: var(--primary-color);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .day-badge.today {
            background: var(--success-color);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .task-count {
            background: var(--light-bg);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .day-tasks {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .mini-task {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: var(--light-bg);
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .mini-task:hover {
            background: #e3f2fd;
            transform: translateX(5px);
        }

        .task-color-bar {
            width: 6px;
            height: 50px;
            border-radius: 3px;
        }

        .task-color-bar.yellow { background: #ffc107; }
        .task-color-bar.blue { background: #2196f3; }
        .task-color-bar.green { background: #4caf50; }
        .task-color-bar.pink { background: #e91e63; }

        .task-info {
            flex: 1;
        }

        .task-title {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
            font-size: 15px;
        }

        .task-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .task-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .task-status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .task-status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .task-status-badge.ongoing {
            background: #cfe2ff;
            color: #084298;
        }

        .task-status-badge.completed {
            background: #d1e7dd;
            color: #0f5132;
        }

        .empty-day {
            text-align: center;
            padding: 30px;
            color: var(--text-muted);
            font-style: italic;
        }

        .empty-week {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
        }

        .empty-week-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .priority-badge {
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-badge.high {
            background: #fee;
            color: #c00;
        }

        .priority-badge.medium {
            background: #fef3cd;
            color: #997404;
        }

        .priority-badge.low {
            background: #e7f5ff;
            color: #0c5da5;
        }
    </style>
</head>
<body>
<div class="wrapper">
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="week-header">
        <h3>📅 Weekly Overview</h3>
        <div class="week-range">
            📍 <?php echo date('M d', strtotime($start)); ?> - <?php echo date('M d, Y', strtotime($end)); ?>
        </div>
    </div>

    <div class="week-stats">
        <div class="week-stat-card">
            <h4><?php echo $stats['total'] ?? 0; ?></h4>
            <p>Total Tasks</p>
        </div>
        <div class="week-stat-card completed">
            <h4><?php echo $stats['completed'] ?? 0; ?></h4>
            <p>Completed</p>
        </div>
        <div class="week-stat-card ongoing">
            <h4><?php echo $stats['ongoing'] ?? 0; ?></h4>
            <p>On-going</p>
        </div>
        <div class="week-stat-card pending">
            <h4><?php echo $stats['pending'] ?? 0; ?></h4>
            <p>Pending</p>
        </div>
    </div>

    <?php if (empty($tasksByDay)): ?>
        <div class="empty-week">
            <div class="empty-week-icon">📅</div>
            <h4>No Tasks This Week</h4>
            <p>You don't have any tasks due this week. Enjoy your free time or plan ahead!</p>
            <a href="add_task.php" class="btn" style="margin-top: 20px;">+ Add New Task</a>
        </div>
    <?php else: ?>
        <div class="days-container">
            <?php
            // Loop through each day of the week
            for ($i = 0; $i < 7; $i++) {
                $currentDay = date('Y-m-d', strtotime($start . ' +' . $i . ' days'));
                $dayName = date('l', strtotime($currentDay));
                $dayDate = date('M d', strtotime($currentDay));
                $isToday = ($currentDay == date('Y-m-d'));
                $isPast = ($currentDay < date('Y-m-d'));
                $dayTasks = $tasksByDay[$currentDay] ?? [];
                
                // Skip days with no tasks (optional - comment out to show all days)
                // if (empty($dayTasks)) continue;
            ?>
                <div class="day-section <?php echo $isToday ? 'today' : ($isPast ? 'past' : ''); ?>">
                    <div class="day-header">
                        <div class="day-title">
                            <?php echo $dayName; ?>
                            <span style="color: var(--text-muted); font-weight: 400; font-size: 14px;">
                                <?php echo $dayDate; ?>
                            </span>
                            <?php if ($isToday): ?>
                                <span class="day-badge today">Today</span>
                            <?php endif; ?>
                        </div>
                        <div class="task-count">
                            <?php echo count($dayTasks); ?> <?php echo count($dayTasks) == 1 ? 'task' : 'tasks'; ?>
                        </div>
                    </div>

                    <?php if (empty($dayTasks)): ?>
                        <div class="empty-day">
                            No tasks scheduled for this day
                        </div>
                    <?php else: ?>
                        <div class="day-tasks">
                            <?php foreach ($dayTasks as $task): ?>
                                <div class="mini-task">
                                    <div class="task-color-bar <?php echo $colors[$task['category']] ?? 'yellow'; ?>"></div>
                                    <div class="task-info">
                                        <div class="task-title">
                                            <?php echo htmlspecialchars($task['title']); ?>
                                        </div>
                                        <div class="task-meta">
                                            <span>📁 <?php echo htmlspecialchars($task['category']); ?></span>
                                            <span>
                                                <span class="priority-badge <?php echo strtolower($task['priority']); ?>">
                                                    <?php echo $task['priority']; ?>
                                                </span>
                                            </span>
                                            <?php if ($task['description']): ?>
                                                <span>📝 <?php echo htmlspecialchars(substr($task['description'], 0, 50)); ?><?php echo strlen($task['description']) > 50 ? '...' : ''; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span class="task-status-badge <?php echo strtolower(str_replace('-', '', $task['status'])); ?>">
                                        <?php echo $task['status']; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php } ?>
        </div>
    <?php endif; ?>
</div>
</div>
</body>
</html>
