<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/* Get user name (session first, fallback to DB) */
$user_name = $_SESSION['user_name'] ?? null;
if (!$user_name) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $user_name = $row['name'] ?? "Student";
    $_SESSION['user_name'] = $user_name;
    $stmt->close();
}

/* Status counts (not archived) */
$stmt = $conn->prepare("
    SELECT 
        SUM(status='Pending')   AS pending_count,
        SUM(status='On-going')  AS ongoing_count,
        SUM(status='Completed') AS completed_count,
        COUNT(*) AS total_count
    FROM tasks
    WHERE user_id = ? AND is_archived = 0
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$counts = $stmt->get_result()->fetch_assoc();
$stmt->close();

$pending   = (int)($counts['pending_count'] ?? 0);
$ongoing   = (int)($counts['ongoing_count'] ?? 0);
$completed = (int)($counts['completed_count'] ?? 0);
$total     = (int)($counts['total_count'] ?? 0);

/* Overdue count (not completed + due date < today) */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS overdue_count
    FROM tasks
    WHERE user_id = ?
      AND is_archived = 0
      AND status != 'Completed'
      AND due_date IS NOT NULL
      AND due_date < CURDATE()
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$overdue = (int)($stmt->get_result()->fetch_assoc()['overdue_count'] ?? 0);
$stmt->close();

/* Left panel: Active tasks (Pending + On-going) */
$stmt = $conn->prepare("
    SELECT id, title, category, priority, due_date, status
    FROM tasks
    WHERE user_id = ?
      AND is_archived = 0
      AND status IN ('Pending','On-going')
    ORDER BY due_date IS NULL, due_date ASC, created_at DESC
    LIMIT 10
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$active_tasks = $stmt->get_result();
$stmt->close();

/* Bottom-right: Completed tasks (not archived) */
$stmt = $conn->prepare("
    SELECT id, title, category, priority, due_date, status
    FROM tasks
    WHERE user_id = ?
      AND is_archived = 0
      AND status = 'Completed'
    ORDER BY created_at DESC
    LIMIT 8
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_tasks = $stmt->get_result();
$stmt->close();

$today_text = date("l, d M Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ToDo Student | Dashboard</title>
    <link rel="stylesheet" href="css/style.css">

    <!-- Minimal dashboard styling (safe to keep here even if your CSS already exists) -->
    <style>
        .dash-header{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:20px;
            margin-bottom:18px;
        }
        .dash-title{ margin:0; font-size:26px; }
        .dash-subtitle{ margin:6px 0 0; color:#777; }
        .dash-date{
            background:#fff;
            padding:10px 14px;
            border-radius:12px;
            box-shadow:0 1px 8px rgba(0,0,0,0.06);
            color:#444;
            font-size:14px;
        }

        .stats-grid-mini{
            display:grid;
            grid-template-columns: repeat(4, 1fr);
            gap:14px;
            margin-bottom:18px;
        }
        .stat-mini{
            background:#fff;
            border-radius:14px;
            padding:14px;
            box-shadow:0 1px 10px rgba(0,0,0,0.06);
        }
        .stat-mini h2{ margin:0; font-size:22px; }
        .stat-mini p{ margin:6px 0 0; color:#777; font-size:13px; }
        .stat-mini.overdue{ border:1px solid #ffb3b3; }

        .dash-grid{
            display:grid;
            grid-template-columns: 2fr 1fr;
            grid-template-rows: auto auto;
            gap:18px;
        }
        .dash-card{
            background:#fff;
            border-radius:16px;
            padding:16px;
            box-shadow:0 1px 10px rgba(0,0,0,0.06);
        }
        .dash-card-large{ grid-row: 1 / span 2; }

        .card-top{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            margin-bottom:12px;
        }

        .dash-table{
            width:100%;
            border-collapse:collapse;
        }
        .dash-table th, .dash-table td{
            padding:10px 8px;
            border-bottom:1px solid #eee;
            text-align:left;
            font-size:14px;
        }
        .muted{ color:#777; }

        .status-box{
            display:grid;
            grid-template-columns:1fr 1fr 1fr;
            gap:10px;
            margin-top:10px;
        }
        .status-item{
            background:#f7f7f7;
            border-radius:12px;
            padding:12px;
            text-align:center;
        }
        .status-num{ font-size:22px; font-weight:700; }
        .status-label{ font-size:13px; color:#666; margin-top:4px; }

        .btn-outline{
            display:inline-block;
            padding:10px 12px;
            border-radius:10px;
            border:1px solid #ddd;
            color:#333;
            text-decoration:none;
            font-size:14px;
            background:transparent;
            cursor:pointer;
        }

        .link{ color:#4a5d23; text-decoration:none; }
        .link:hover{ text-decoration:underline; }

        .completed-list{
            list-style:none;
            padding:0;
            margin:0;
        }
        .completed-item{
            display:flex;
            justify-content:space-between;
            gap:12px;
            padding:10px 0;
            border-bottom:1px solid #eee;
        }
        .completed-title{ font-weight:600; }
        .completed-meta{ font-size:12px; color:#777; margin-top:4px; }
    </style>
</head>

<body>
<div class="wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main">

        <!-- HEADER -->
        <div class="dash-header">
            <div>
                <h2 class="dash-title">Welcome back, <?php echo htmlspecialchars($user_name); ?> 👋</h2>
                <p class="dash-subtitle">Here is your dashboard overview.</p>
            </div>
            <div class="dash-date"><?php echo htmlspecialchars($today_text); ?></div>
        </div>

        <!-- MINI STATS ROW (optional but nice) -->
        <div class="stats-grid-mini">
            <div class="stat-mini">
                <h2><?php echo $total; ?></h2>
                <p>Total Tasks</p>
            </div>
            <div class="stat-mini">
                <h2><?php echo $ongoing; ?></h2>
                <p>On-going</p>
            </div>
            <div class="stat-mini">
                <h2><?php echo $pending; ?></h2>
                <p>Pending</p>
            </div>
            <div class="stat-mini <?php echo $overdue > 0 ? 'overdue' : ''; ?>">
                <h2><?php echo $overdue; ?></h2>
                <p>Overdue</p>
            </div>
        </div>

        <!-- MAIN GRID -->
        <div class="dash-grid">

            <!-- LEFT: TASK LIST -->
            <div class="dash-card dash-card-large">
                <div class="card-top">
                    <h3>Task List</h3>
                    <a class="btn" href="add_task.php">+ Add New Task</a>
                </div>

                <?php if ($active_tasks->num_rows > 0): ?>
                    <table class="dash-table">
                        <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($t = $active_tasks->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($t['title']); ?></td>
                                <td><?php echo htmlspecialchars($t['category'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($t['priority'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($t['due_date'] ?? '-'); ?></td>
                                <td>
                                    <span class="status <?php echo strtolower(str_replace('-', '', $t['status'])); ?>">
                                        <?php echo htmlspecialchars($t['status']); ?>
                                    </span>
                                </td>

                                <td style="text-align:right; white-space:nowrap;">
                                    <a class="link" href="edit_task.php?id=<?php echo (int)$t['id']; ?>">Edit</a>
                                    |
                                    <a class="link" href="view_tasks.php">View</a>

                                    <!-- Status dropdown (POST to update_status.php) -->
                                    <form action="update_status.php" method="POST" style="display:inline-block; margin-left:10px;">
                                        <input type="hidden" name="task_id" value="<?php echo (int)$t['id']; ?>">
                                        <select name="new_status" onchange="this.form.submit()"
                                                style="padding:6px; border-radius:8px;">
                                            <option value="" selected disabled>Status</option>
                                            <option value="Pending">Pending</option>
                                            <option value="On-going">On-going</option>
                                            <option value="Completed">Completed</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="muted">No active tasks yet. <a href="add_task.php">Add your first task</a>.</p>
                <?php endif; ?>
            </div>

            <!-- RIGHT TOP: TASK STATUS -->
            <div class="dash-card">
                <h3>Task Status</h3>
                <div class="status-box">
                    <div class="status-item">
                        <div class="status-num"><?php echo $pending; ?></div>
                        <div class="status-label">Pending</div>
                    </div>
                    <div class="status-item">
                        <div class="status-num"><?php echo $ongoing; ?></div>
                        <div class="status-label">On-going</div>
                    </div>
                    <div class="status-item">
                        <div class="status-num"><?php echo $completed; ?></div>
                        <div class="status-label">Completed</div>
                    </div>
                </div>

                <div style="margin-top:12px;">
                    <a class="btn-outline" href="view_tasks.php">Open Task Page</a>
                </div>
            </div>

            <!-- RIGHT BOTTOM: COMPLETED TASKS -->
            <div class="dash-card">
                <div class="card-top">
                    <h3>Completed Tasks</h3>
                    <a class="btn-outline" href="archive.php">Go Archive</a>
                </div>

                <?php if ($completed_tasks->num_rows > 0): ?>
                    <ul class="completed-list">
                        <?php while ($c = $completed_tasks->fetch_assoc()): ?>
                            <li class="completed-item">
                                <div>
                                    <div class="completed-title"><?php echo htmlspecialchars($c['title']); ?></div>
                                    <div class="completed-meta">
                                        <?php echo htmlspecialchars($c['category'] ?? '-'); ?> •
                                        <?php echo htmlspecialchars($c['priority'] ?? '-'); ?> •
                                        Due: <?php echo htmlspecialchars($c['due_date'] ?? '-'); ?>
                                    </div>
                                </div>

                                <div style="white-space:nowrap;">
                                    <!-- Optional reopen button -->
                                    <form action="update_status.php" method="POST" style="display:inline-block;">
                                        <input type="hidden" name="task_id" value="<?php echo (int)$c['id']; ?>">
                                        <input type="hidden" name="new_status" value="On-going">
                                        <button type="submit" class="btn-outline" style="padding:6px 10px;">Reopen</button>
                                    </form>

                                    <!-- Archive link -->
                                    <a class="btn-outline" style="padding:6px 10px; margin-left:6px;"
                                       href="archive_task.php?id=<?php echo (int)$c['id']; ?>">
                                        Archive
                                    </a>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="muted">No completed tasks yet.</p>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>
</body>
</html>
