<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];
    $add_another = isset($_POST['add_another']) ? true : false;

    $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, category, priority, due_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $title, $description, $category, $priority, $due_date, $status);

    if ($stmt->execute()) {
        $stmt->close();
        
        if ($add_another) {
            // Stay on page but show success and clear form
            $_SESSION['task_success'] = "Task \"" . htmlspecialchars($title) . "\" added! Add another task below.";
            header("Location: add_task.php");
            exit();
        } else {
            // Redirect to task list with success message
            $_SESSION['task_success'] = "Task \"" . htmlspecialchars($title) . "\" has been created successfully!";
            header("Location: view_tasks.php");
            exit();
        }
    } else {
        $error = "Failed to add task. Please try again.";
    }
    $stmt->close();
}

// Check for success message from redirect
$success = "";
if (isset($_SESSION['task_success'])) {
    $success = $_SESSION['task_success'];
    unset($_SESSION['task_success']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDo Student | Add Task</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .add-task-container {
            max-width: 600px;
        }

        .add-task-header {
            margin-bottom: 25px;
        }

        .add-task-header h3 {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 8px 0;
        }

        .add-task-header p {
            color: var(--text-muted);
            font-size: 14px;
            margin: 0;
        }

        .success-banner {
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

        .success-banner .icon {
            font-size: 24px;
        }

        .success-banner .message {
            flex: 1;
            color: #2e7d32;
            font-weight: 500;
        }

        .error-banner {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            border: 1px solid #ef9a9a;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .error-banner .icon {
            font-size: 24px;
        }

        .error-banner .message {
            flex: 1;
            color: #c62828;
            font-weight: 500;
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

        .form-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-group label .label-icon {
            font-size: 14px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .btn-add-another {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .btn-add-another:hover {
            background: #c8e6c9;
        }

        .char-count {
            font-size: 11px;
            color: var(--text-muted);
            text-align: right;
            margin-top: 4px;
        }

        .quick-tips {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 25px;
        }

        .quick-tips h4 {
            font-size: 14px;
            color: var(--text-color);
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quick-tips ul {
            margin: 0;
            padding-left: 20px;
            color: var(--text-muted);
            font-size: 13px;
        }

        .quick-tips li {
            margin-bottom: 6px;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="main">
            <div class="add-task-header">
                <h3>✏️ Add New Task</h3>
                <p>Create a new task to stay organized with your academic work</p>
            </div>

            <div class="add-task-container">
                <?php if ($success): ?>
                    <div class="success-banner">
                        <span class="icon">✅</span>
                        <span class="message"><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-banner">
                        <span class="icon">❌</span>
                        <span class="message"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <form method="post" action="" id="addTaskForm">
                        <div class="form-group">
                            <label>
                                <span class="label-icon">📝</span> Title
                            </label>
                            <input type="text" name="title" id="titleInput" required maxlength="200" 
                                   placeholder="Enter task title (e.g., Complete Math Assignment)">
                            <div class="char-count"><span id="titleCount">0</span>/200 characters</div>
                        </div>

                        <div class="form-group">
                            <label>
                                <span class="label-icon">📋</span> Description
                            </label>
                            <textarea name="description" rows="3" 
                                      placeholder="Add details about this task (optional)"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    <span class="label-icon">📁</span> Category
                                </label>
                                <select name="category" required>
                                    <option value="Assignment">📝 Assignment</option>
                                    <option value="Discussion">💬 Discussion</option>
                                    <option value="Club Activity">🎯 Club Activity</option>
                                    <option value="Examination">📚 Examination</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>
                                    <span class="label-icon">🎚️</span> Priority
                                </label>
                                <select name="priority" required>
                                    <option value="Low">🟢 Low</option>
                                    <option value="Medium" selected>🟡 Medium</option>
                                    <option value="High">🔴 High</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    <span class="label-icon">📅</span> Due Date
                                </label>
                                <input type="date" name="due_date" id="dueDateInput" required 
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="form-group">
                                <label>
                                    <span class="label-icon">📊</span> Status
                                </label>
                                <select name="status" required>
                                    <option value="Pending" selected>⏳ Pending</option>
                                    <option value="On-going">🔄 On-going</option>
                                    <option value="Completed">✅ Completed</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="submit" class="btn">
                                ✓ Create Task
                            </button>
                            <button type="submit" name="submit" class="btn btn-add-another" 
                                    onclick="document.getElementById('addAnother').value='1';">
                                ➕ Create & Add Another
                            </button>
                            <a href="view_tasks.php" class="btn btn-secondary">
                                ✕ Cancel
                            </a>
                            <input type="hidden" name="add_another" id="addAnother" value="0">
                        </div>
                    </form>
                </div>

                <div class="quick-tips">
                    <h4>💡 Quick Tips</h4>
                    <ul>
                        <li>Set <strong>High priority</strong> for urgent deadlines like exams</li>
                        <li>Use <strong>categories</strong> to organize tasks by type</li>
                        <li>Click <strong>"Create & Add Another"</strong> to quickly add multiple tasks</li>
                        <li>Tasks will appear in <strong>My Tasks</strong> after creation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Character counter for title
        const titleInput = document.getElementById('titleInput');
        const titleCount = document.getElementById('titleCount');
        
        titleInput.addEventListener('input', function() {
            titleCount.textContent = this.value.length;
        });

        // Set minimum date to today
        const dueDateInput = document.getElementById('dueDateInput');
        const today = new Date().toISOString().split('T')[0];
        dueDateInput.setAttribute('min', today);

        // Handle "Create & Add Another" button
        const addAnotherInput = document.getElementById('addAnother');
        const form = document.getElementById('addTaskForm');
        
        // Reset the hidden field when form is submitted normally
        form.addEventListener('submit', function(e) {
            // The onclick already sets the value for "Add Another" button
            // For the regular submit button, we want it to be 0
            if (!e.submitter || !e.submitter.classList.contains('btn-add-another')) {
                addAnotherInput.value = '0';
            }
        });

        // Auto-hide success message after 5 seconds
        const successBanner = document.querySelector('.success-banner');
        if (successBanner) {
            setTimeout(() => {
                successBanner.style.transition = 'opacity 0.3s, transform 0.3s';
                successBanner.style.opacity = '0';
                successBanner.style.transform = 'translateY(-10px)';
                setTimeout(() => successBanner.remove(), 300);
            }, 5000);
        }
    </script>

</body>

</html>