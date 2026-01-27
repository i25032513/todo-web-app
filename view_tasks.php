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
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT * FROM tasks WHERE user_id = ? AND is_archived = 0";
$params = [$user_id];
$types = "i";

if ($search_query) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}
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
    if ($sort_by == 'priority') {
        $sql .= " ORDER BY FIELD(priority, 'High', 'Medium', 'Low')";
    } else {
        $sql .= " ORDER BY $sort_by ASC";
    }
} else {
    $sql .= " ORDER BY due_date ASC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$tasks = $stmt->get_result();

// Get task statistics
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'On-going' THEN 1 ELSE 0 END) as ongoing,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN due_date < CURDATE() AND status != 'Completed' THEN 1 ELSE 0 END) as overdue
    FROM tasks WHERE user_id = ? AND is_archived = 0";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

$colors = ['Assignment' => 'yellow', 'Discussion' => 'blue', 'Club Activity' => 'green', 'Examination' => 'pink'];
$category_icons = ['Assignment' => '📝', 'Discussion' => '💬', 'Club Activity' => '🎯', 'Examination' => '📚'];

// Check for success message from redirect (e.g., after creating a task)
$flash_success = "";
if (isset($_SESSION['task_success'])) {
    $flash_success = $_SESSION['task_success'];
    unset($_SESSION['task_success']);
}

// Helper function to determine due date status
function getDueDateClass($due_date, $status) {
    if ($status == 'Completed') return 'due-completed';
    $today = new DateTime();
    $due = new DateTime($due_date);
    $diff = $today->diff($due);
    $days = (int)$diff->format('%r%a');
    
    if ($days < 0) return 'due-overdue';
    if ($days == 0) return 'due-today';
    if ($days <= 3) return 'due-soon';
    return 'due-normal';
}

function getDueDateLabel($due_date, $status) {
    if ($status == 'Completed') return '';
    $today = new DateTime();
    $due = new DateTime($due_date);
    $diff = $today->diff($due);
    $days = (int)$diff->format('%r%a');
    
    if ($days < 0) return 'Overdue by ' . abs($days) . ' day(s)';
    if ($days == 0) return 'Due Today!';
    if ($days == 1) return 'Due Tomorrow';
    if ($days <= 3) return 'Due in ' . $days . ' days';
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDo Student | My Tasks</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Enhanced Task Page Styles */
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .task-header-left h3 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
            margin: 0;
        }

        .task-header-left p {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 5px;
        }

        .task-header-right {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        /* Task Stats Mini Cards */
        .task-stats {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .task-stat-item {
            background: #ffffff;
            padding: 15px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            min-width: 140px;
        }

        .task-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .task-stat-icon.total { background: #e3f2fd; }
        .task-stat-icon.completed { background: #e8f5e9; }
        .task-stat-icon.ongoing { background: #fff3e0; }
        .task-stat-icon.pending { background: #f3e5f5; }
        .task-stat-icon.overdue { background: #ffebee; }

        .task-stat-info h4 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            color: var(--text-color);
        }

        .task-stat-info p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
        }

        /* Search and Filter Bar */
        .search-filter-bar {
            background: #ffffff;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 25px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .search-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            align-items: center;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.2s;
            background: var(--light-bg);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-box::before {
            content: "🔍";
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
        }

        .view-toggle {
            display: flex;
            background: var(--light-bg);
            border-radius: 8px;
            padding: 4px;
        }

        .view-toggle button {
            padding: 8px 15px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.2s;
        }

        .view-toggle button.active {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filter-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-row select {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 13px;
            background: var(--light-bg);
            cursor: pointer;
            min-width: 150px;
        }

        .filter-row select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        /* Enhanced Task Cards */
        .task-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .task-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .task-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .task-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .task-card.yellow { border-left: 4px solid #ffc107; }
        .task-card.blue { border-left: 4px solid #2196f3; }
        .task-card.green { border-left: 4px solid #4caf50; }
        .task-card.pink { border-left: 4px solid #e91e63; }

        .task-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .task-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
            flex: 1;
            margin-right: 10px;
        }

        .task-description {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 15px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .task-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 15px;
        }

        .task-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .task-meta-item span {
            font-weight: 500;
        }

        .due-date-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }

        .due-overdue {
            background: #ffebee;
            color: #c62828;
        }

        .due-today {
            background: #fff3e0;
            color: #ef6c00;
        }

        .due-soon {
            background: #e3f2fd;
            color: #1565c0;
        }

        .due-normal {
            background: var(--light-bg);
            color: var(--text-muted);
        }

        .due-completed {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .task-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }

        .task-status-select {
            padding: 6px 10px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 12px;
            background: var(--light-bg);
            cursor: pointer;
        }

        .task-actions {
            display: flex;
            gap: 8px;
        }

        .task-action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }

        .task-action-btn.edit {
            background: #e3f2fd;
            color: #1976d2;
        }

        .task-action-btn.edit:hover {
            background: #bbdefb;
        }

        .task-action-btn.archive {
            background: #fff3e0;
            color: #f57c00;
        }

        .task-action-btn.archive:hover {
            background: #ffe0b2;
        }

        .task-action-btn.delete {
            background: #ffebee;
            color: #c62828;
        }

        .task-action-btn.delete:hover {
            background: #ffcdd2;
        }

        /* List View Specific */
        .task-list .task-card {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 15px 20px;
        }

        .task-list .task-card-header {
            flex: 1;
            margin-bottom: 0;
        }

        .task-list .task-description {
            display: none;
        }

        .task-list .task-meta {
            margin-bottom: 0;
            flex-shrink: 0;
        }

        .task-list .task-footer {
            border-top: none;
            padding-top: 0;
            flex-shrink: 0;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            font-size: 20px;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--text-muted);
            margin-bottom: 25px;
        }

        /* Category Badge */
        .category-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            background: var(--light-bg);
            color: var(--text-muted);
        }

        /* Progress Bar */
        .progress-bar-container {
            background: var(--light-bg);
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        /* Checkbox for completion */
        .task-checkbox {
            width: 22px;
            height: 22px;
            border: 2px solid var(--border-color);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .task-checkbox:hover {
            border-color: var(--primary-color);
        }

        .task-checkbox.completed {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }

        /* Delete Confirmation Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: #ffffff;
            padding: 30px;
            border-radius: 16px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .modal-content h4 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .modal-content p {
            color: var(--text-muted);
            margin-bottom: 25px;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .task-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .task-stats {
                flex-direction: column;
            }

            .task-stat-item {
                width: 100%;
            }

            .search-row {
                flex-direction: column;
            }

            .filter-row {
                flex-direction: column;
            }

            .filter-row select {
                width: 100%;
            }

            .filter-actions {
                margin-left: 0;
                width: 100%;
            }

            .filter-actions .btn {
                flex: 1;
            }

            .task-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Dark mode support */
        body.dark-mode .task-card,
        body.dark-mode .search-filter-bar,
        body.dark-mode .task-stat-item,
        body.dark-mode .empty-state,
        body.dark-mode .modal-content {
            background: #2d2d2d;
            border-color: #444;
        }

        body.dark-mode .search-box input,
        body.dark-mode .filter-row select,
        body.dark-mode .task-status-select {
            background: #333;
            border-color: #444;
            color: #fff;
        }

        /* Flash Success Banner */
        .flash-success {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border: 1px solid #a5d6a7;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        .flash-success .icon {
            font-size: 24px;
        }

        .flash-success .message {
            flex: 1;
            color: #2e7d32;
            font-weight: 500;
        }

        .flash-success .close-btn {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #2e7d32;
            padding: 5px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .flash-success .close-btn:hover {
            opacity: 1;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="main">
            <!-- Flash Success Message -->
            <?php if ($flash_success): ?>
                <div class="flash-success" id="flashSuccess">
                    <span class="icon">✅</span>
                    <span class="message"><?php echo $flash_success; ?></span>
                    <button class="close-btn" onclick="closeFlashMessage()">✕</button>
                </div>
            <?php endif; ?>

            <!-- Enhanced Header -->
            <div class="task-header">
                <div class="task-header-left">
                    <h3>📋 My Tasks</h3>
                    <p>Manage and track all your tasks in one place</p>
                </div>
                <div class="task-header-right">
                    <a href="add_task.php" class="btn">+ Add New Task</a>
                </div>
            </div>

            <!-- Task Statistics -->
            <div class="task-stats">
                <div class="task-stat-item">
                    <div class="task-stat-icon total">📊</div>
                    <div class="task-stat-info">
                        <h4><?php echo $stats['total']; ?></h4>
                        <p>Total Tasks</p>
                    </div>
                </div>
                <div class="task-stat-item">
                    <div class="task-stat-icon completed">✅</div>
                    <div class="task-stat-info">
                        <h4><?php echo $stats['completed']; ?></h4>
                        <p>Completed</p>
                    </div>
                </div>
                <div class="task-stat-item">
                    <div class="task-stat-icon ongoing">🔄</div>
                    <div class="task-stat-info">
                        <h4><?php echo $stats['ongoing']; ?></h4>
                        <p>On-going</p>
                    </div>
                </div>
                <div class="task-stat-item">
                    <div class="task-stat-icon pending">⏳</div>
                    <div class="task-stat-info">
                        <h4><?php echo $stats['pending']; ?></h4>
                        <p>Pending</p>
                    </div>
                </div>
                <?php if ($stats['overdue'] > 0): ?>
                <div class="task-stat-item">
                    <div class="task-stat-icon overdue">⚠️</div>
                    <div class="task-stat-info">
                        <h4><?php echo $stats['overdue']; ?></h4>
                        <p>Overdue</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Progress Bar -->
            <?php if ($stats['total'] > 0): ?>
            <div class="card" style="padding: 15px 20px; margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 14px; font-weight: 500;">Overall Progress</span>
                    <span style="font-size: 14px; color: var(--primary-color); font-weight: 600;">
                        <?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?>%
                    </span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?php echo $stats['total'] > 0 ? ($stats['completed'] / $stats['total']) * 100 : 0; ?>%;"></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Search and Filter Bar -->
            <form method="get" class="search-filter-bar">
                <div class="search-row">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Search tasks by title or description..." 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    <div class="view-toggle">
                        <button type="button" class="active" onclick="setView('grid')" title="Grid View">⊞</button>
                        <button type="button" onclick="setView('list')" title="List View">☰</button>
                    </div>
                </div>
                <div class="filter-row">
                    <select name="category">
                        <option value="">📁 All Categories</option>
                        <option value="Assignment" <?php echo $category_filter == 'Assignment' ? 'selected' : ''; ?>>📝 Assignment</option>
                        <option value="Discussion" <?php echo $category_filter == 'Discussion' ? 'selected' : ''; ?>>💬 Discussion</option>
                        <option value="Club Activity" <?php echo $category_filter == 'Club Activity' ? 'selected' : ''; ?>>🎯 Club Activity</option>
                        <option value="Examination" <?php echo $category_filter == 'Examination' ? 'selected' : ''; ?>>📚 Examination</option>
                    </select>
                    <select name="priority">
                        <option value="">🎚️ All Priorities</option>
                        <option value="High" <?php echo $priority_filter == 'High' ? 'selected' : ''; ?>>🔴 High</option>
                        <option value="Medium" <?php echo $priority_filter == 'Medium' ? 'selected' : ''; ?>>🟡 Medium</option>
                        <option value="Low" <?php echo $priority_filter == 'Low' ? 'selected' : ''; ?>>🟢 Low</option>
                    </select>
                    <select name="status">
                        <option value="">📊 All Status</option>
                        <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                        <option value="On-going" <?php echo $status_filter == 'On-going' ? 'selected' : ''; ?>>🔄 On-going</option>
                        <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?>>✅ Completed</option>
                    </select>
                    <select name="sort">
                        <option value="due_date" <?php echo $sort_by == 'due_date' ? 'selected' : ''; ?>>📅 Sort by Due Date</option>
                        <option value="priority" <?php echo $sort_by == 'priority' ? 'selected' : ''; ?>>🎚️ Sort by Priority</option>
                        <option value="created_at" <?php echo $sort_by == 'created_at' ? 'selected' : ''; ?>>🕐 Sort by Created</option>
                        <option value="title" <?php echo $sort_by == 'title' ? 'selected' : ''; ?>>🔤 Sort by Title</option>
                    </select>
                    <div class="filter-actions">
                        <button type="submit" class="btn">Apply Filters</button>
                        <a href="view_tasks.php" class="btn btn-secondary">Clear All</a>
                    </div>
                </div>
            </form>

            <!-- Task Results Info -->
            <?php if ($tasks->num_rows > 0): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <p style="color: var(--text-muted); font-size: 14px;">
                    Showing <strong><?php echo $tasks->num_rows; ?></strong> task(s)
                    <?php if ($search_query || $category_filter || $priority_filter || $status_filter): ?>
                        with applied filters
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Tasks Container -->
            <?php if ($tasks->num_rows > 0): ?>
                <div class="task-grid" id="taskContainer">
                    <?php while ($task = $tasks->fetch_assoc()): 
                        $dueDateClass = getDueDateClass($task['due_date'], $task['status']);
                        $dueDateLabel = getDueDateLabel($task['due_date'], $task['status']);
                    ?>
                        <div class="task-card <?php echo $colors[$task['category']] ?? 'yellow'; ?>" data-task-id="<?php echo $task['id']; ?>">
                            <div class="task-card-header">
                                <div style="display: flex; align-items: flex-start; gap: 12px; flex: 1;">
                                    <div class="task-checkbox <?php echo $task['status'] == 'Completed' ? 'completed' : ''; ?>"
                                         onclick="toggleComplete(<?php echo $task['id']; ?>, '<?php echo $task['status']; ?>')"
                                         title="<?php echo $task['status'] == 'Completed' ? 'Mark as Pending' : 'Mark as Completed'; ?>">
                                        <?php echo $task['status'] == 'Completed' ? '✓' : ''; ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <h4 class="task-title" style="<?php echo $task['status'] == 'Completed' ? 'text-decoration: line-through; color: var(--text-muted);' : ''; ?>">
                                            <?php echo htmlspecialchars($task['title']); ?>
                                        </h4>
                                        <p class="task-description">
                                            <?php echo htmlspecialchars($task['description'] ?? 'No description'); ?>
                                        </p>
                                    </div>
                                </div>
                                <span class="priority-badge <?php echo strtolower($task['priority'] ?? 'medium'); ?>">
                                    <?php echo $task['priority'] ?? 'Medium'; ?>
                                </span>
                            </div>

                            <div class="task-meta">
                                <div class="task-meta-item">
                                    <span class="category-badge">
                                        <?php echo $category_icons[$task['category']] ?? '📌'; ?>
                                        <?php echo $task['category']; ?>
                                    </span>
                                </div>
                                <div class="task-meta-item">
                                    📅 <span><?php echo date('M d, Y', strtotime($task['due_date'])); ?></span>
                                </div>
                                <?php if ($dueDateLabel): ?>
                                <div class="due-date-badge <?php echo $dueDateClass; ?>">
                                    <?php echo $dueDateLabel; ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="task-footer">
                                <form method="post" action="update_status.php" style="display: inline;">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <select name="new_status" class="task-status-select" onchange="this.form.submit()">
                                        <option value="Pending" <?php echo $task['status'] == 'Pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                        <option value="On-going" <?php echo $task['status'] == 'On-going' ? 'selected' : ''; ?>>🔄 On-going</option>
                                        <option value="Completed" <?php echo $task['status'] == 'Completed' ? 'selected' : ''; ?>>✅ Completed</option>
                                    </select>
                                </form>
                                <div class="task-actions">
                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="task-action-btn edit" title="Edit Task">
                                        ✏️ Edit
                                    </a>
                                    <a href="archive_task.php?id=<?php echo $task['id']; ?>" class="task-action-btn archive" title="Archive Task">
                                        📦 Archive
                                    </a>
                                    <button type="button" class="task-action-btn delete" onclick="confirmDelete(<?php echo $task['id']; ?>)" title="Delete Task">
                                        🗑️ Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <h4>No tasks found</h4>
                    <p>
                        <?php if ($search_query || $category_filter || $priority_filter || $status_filter): ?>
                            Try adjusting your filters or search query
                        <?php else: ?>
                            Start organizing your academic life by adding your first task
                        <?php endif; ?>
                    </p>
                    <a href="add_task.php" class="btn">+ Create New Task</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-content">
            <h4>🗑️ Delete Task?</h4>
            <p>Are you sure you want to delete this task? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>

    <script>
        // View Toggle
        function setView(view) {
            const container = document.getElementById('taskContainer');
            const buttons = document.querySelectorAll('.view-toggle button');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            if (view === 'list') {
                container.classList.remove('task-grid');
                container.classList.add('task-list');
            } else {
                container.classList.remove('task-list');
                container.classList.add('task-grid');
            }
            
            localStorage.setItem('taskView', view);
        }

        // Load saved view preference
        document.addEventListener('DOMContentLoaded', function() {
            const savedView = localStorage.getItem('taskView');
            if (savedView === 'list') {
                setView('list');
                document.querySelectorAll('.view-toggle button')[1].classList.add('active');
                document.querySelectorAll('.view-toggle button')[0].classList.remove('active');
            }
        });

        // Quick complete toggle
        function toggleComplete(taskId, currentStatus) {
            const newStatus = currentStatus === 'Completed' ? 'Pending' : 'Completed';
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'update_status.php';
            
            const taskIdInput = document.createElement('input');
            taskIdInput.type = 'hidden';
            taskIdInput.name = 'task_id';
            taskIdInput.value = taskId;
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'new_status';
            statusInput.value = newStatus;
            
            form.appendChild(taskIdInput);
            form.appendChild(statusInput);
            document.body.appendChild(form);
            form.submit();
        }

        // Delete confirmation modal
        function confirmDelete(taskId) {
            const modal = document.getElementById('deleteModal');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.href = 'delete_task.php?id=' + taskId;
            modal.classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Close modal on outside click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Keyboard shortcut to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });

        // Real-time search (with debounce)
        let searchTimeout;
        const searchInput = document.querySelector('.search-box input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    // For real-time search, you could implement AJAX here
                    // For now, we'll use form submission on Enter key
                }, 500);
            });
        }

        // Flash message close and auto-hide
        function closeFlashMessage() {
            const flash = document.getElementById('flashSuccess');
            if (flash) {
                flash.style.transition = 'opacity 0.3s, transform 0.3s';
                flash.style.opacity = '0';
                flash.style.transform = 'translateY(-10px)';
                setTimeout(() => flash.remove(), 300);
            }
        }

        // Auto-hide flash message after 5 seconds
        const flashSuccess = document.getElementById('flashSuccess');
        if (flashSuccess) {
            setTimeout(() => {
                closeFlashMessage();
            }, 5000);
        }
    </script>

</body>

</html>