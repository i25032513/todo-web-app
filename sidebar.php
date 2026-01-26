<?php
if (!isset($_SESSION)) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <h2>ToDo Student</h2>
    <ul>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="dashboard.php" <?php echo $current_page == 'dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a>
            </li>
            <li><a href="add_task.php" <?php echo $current_page == 'add_task.php' ? 'class="active"' : ''; ?>>Add Task</a>
            </li>
            <li><a href="view_tasks.php" <?php echo $current_page == 'view_tasks.php' ? 'class="active"' : ''; ?>>View
                    Tasks</a></li>
            <li><a href="archive.php" <?php echo $current_page == 'archive.php' ? 'class="active"' : ''; ?>>Archive</a></li>
            <li><a href="profile.php" <?php echo $current_page == 'profile.php' ? 'class="active"' : ''; ?>>Profile</a></li>
            <li><a href="about.php" <?php echo $current_page == 'about.php' ? 'class="active"' : ''; ?>>About</a></li>
            <li><a href="contact.php" <?php echo $current_page == 'contact.php' ? 'class="active"' : ''; ?>>Contact</a></li>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Home</a></li>
            <li><a href="login.php" <?php echo $current_page == 'login.php' ? 'class="active"' : ''; ?>>Login</a></li>
            <li><a href="register.php" <?php echo $current_page == 'register.php' ? 'class="active"' : ''; ?>>Register</a>
            </li>
            <li><a href="about.php" <?php echo $current_page == 'about.php' ? 'class="active"' : ''; ?>>About</a></li>
        <?php endif; ?>
    </ul>
    <?php if (isset($_SESSION['user_name'])): ?>
        <div class="user-info">
            <p>Welcome,
                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </p>
        </div>
    <?php endif; ?>
</div>