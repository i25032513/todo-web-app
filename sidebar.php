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
<script>
// Apply dark mode site-wide using session value, falling back to localStorage for anonymous pages
(function(){
    try {
        var serverDark = <?php echo (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode']) ? 'true' : 'false'; ?>;
        var stored = localStorage.getItem('dark_mode');

        // If server explicitly set dark mode, it should override localStorage
        if (serverDark === true || serverDark === 'true') {
            document.body.classList.add('dark-mode');
            localStorage.setItem('dark_mode','1');
        } else {
            // Server says dark mode is off: ensure we remove class and clear localStorage
            document.body.classList.remove('dark-mode');
            localStorage.setItem('dark_mode','0');
            // If no server preference and localStorage explicitly requests dark, respect it
            if ((stored === '1') && (serverDark === false || serverDark === 'false')) {
                // only apply if server didn't explicitly set it (rare)
                // but since serverDark is false, we purposely avoid re-applying
            }
        }

        // Inject runtime overrides to ensure cards are dark when class present
        var cssId = 'dark-mode-runtime-overrides';
        if (!document.getElementById(cssId)) {
            var style = document.createElement('style');
            style.id = cssId;
            style.innerHTML = '\nbody.dark-mode:not(.archive-page) .card, body.dark-mode:not(.archive-page) .dash-card, body.dark-mode:not(.archive-page) .stat-card, body.dark-mode:not(.archive-page) .sticky, body.dark-mode:not(.archive-page) .hero, body.dark-mode:not(.archive-page) .features .card, body.dark-mode:not(.archive-page) .add-task-container, body.dark-mode:not(.archive-page) .profile-card, body.dark-mode:not(.archive-page) .auth-card, body.dark-mode:not(.archive-page) .filters { background-color: #1e1e1e !important; color: #e0e0e0 !important; border-color: #333 !important; }\nbody.dark-mode:not(.archive-page) .card *:not(button):not(.btn):not(.btn-primary):not(.btn-secondary):not(.btn-danger), body.dark-mode:not(.archive-page) .dash-card *:not(button):not(.btn):not(.btn-primary):not(.btn-secondary):not(.btn-danger), body.dark-mode:not(.archive-page) .stat-card *:not(button):not(.btn):not(.btn-primary):not(.btn-secondary):not(.btn-danger), body.dark-mode:not(.archive-page) .sticky *:not(button):not(.btn):not(.btn-primary):not(.btn-secondary):not(.btn-danger), body.dark-mode:not(.archive-page) .hero *:not(button):not(.btn):not(.btn-primary):not(.btn-secondary):not(.btn-danger), body.dark-mode:not(.archive-page) .features .card *:not(button):not(.btn):not(.btn-primary):not(.btn-secondary):not(.btn-danger), body.dark-mode:not(.archive-page) .add-task-container *:not(button):not(.btn):not(.btn-primary):not(.btn-secondary):not(.btn-danger), body.dark-mode:not(.archive-page) .profile-card *:not(button):not(.btn):not(.btn-primary):not(.btn-secondary):not(.btn-danger), body.dark-mode:not(.archive-page) .auth-card *:not(button):not(.btn):not(.btn-primary):not(.btn-secondary):not(.btn-danger) { color: #e0e0e0 !important; background: transparent !important; border-color: #444 !important; }\n\n/* Ensure progress bar visible when runtime CSS injected */\nbody.dark-mode:not(.archive-page) .progress-bar-container { background: rgba(255,255,255,0.04) !important; border-radius: 10px !important; height: 8px !important; overflow: hidden !important; margin-top: 5px !important; border: 1px solid rgba(255,255,255,0.04) !important; }\nbody.dark-mode:not(.archive-page) .progress-bar { background: linear-gradient(90deg, var(--primary-color), var(--success-color)) !important; height: 100% !important; border-radius: 10px !important; transition: width 0.3s ease !important; }\n';
            document.head.appendChild(style);
        }
    } catch (e) {
        // ignore
    }
})();
</script>
