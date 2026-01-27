<?php
if (!isset($_SESSION)) session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <h2>ToDo Student</h2>

    <ul>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="dashboard.php" class="<?php echo $current_page=='dashboard.php'?'active':''; ?>">Dashboard</a></li>
            <li><a href="add_task.php" class="<?php echo $current_page=='add_task.php'?'active':''; ?>">+ Add New Task</a></li>

            <li><a href="view_tasks.php" class="<?php echo $current_page=='view_tasks.php'?'active':''; ?>">My Task</a></li>

            <!-- New display pages (create later) -->
            <li><a href="upcoming.php" class="<?php echo $current_page=='upcoming.php'?'active':''; ?>">Upcoming</a></li>
            <li><a href="calendar.php" class="<?php echo $current_page=='calendar.php'?'active':''; ?>">Calendar</a></li>
            <li><a href="priority.php" class="<?php echo $current_page=='priority.php'?'active':''; ?>">Priority</a></li>
            <li><a href="sticky_wall.php" class="<?php echo $current_page=='sticky_wall.php'?'active':''; ?>">Sticky Wall</a></li>
            <li><a href="weekly_overview.php" class="<?php echo $current_page=='weekly_overview.php'?'active':''; ?>">Weekly Overview</a></li>

            <li><a href="archive.php" class="<?php echo $current_page=='archive.php'?'active':''; ?>">Archived Tasks</a></li>

            <li><a href="profile.php" class="<?php echo $current_page=='profile.php'?'active':''; ?>">Profile</a></li>
            <li><a href="settings.php" class="<?php echo $current_page=='settings.php'?'active':''; ?>">Settings</a></li>

            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="index.php" class="<?php echo $current_page=='index.php'?'active':''; ?>">Home</a></li>
            <li><a href="login.php" class="<?php echo $current_page=='login.php'?'active':''; ?>">Login</a></li>
            <li><a href="register.php" class="<?php echo $current_page=='register.php'?'active':''; ?>">Register</a></li>
            <li><a href="about.php" class="<?php echo $current_page=='about.php'?'active':''; ?>">About</a></li>
            <li><a href="contact.php" class="<?php echo $current_page=='contact.php'?'active':''; ?>">Contact</a></li>
        <?php endif; ?>
    </ul>

    <?php if (isset($_SESSION['user_name'])): ?>
        <div class="user-info">
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>
    <?php endif; ?>
</div>
