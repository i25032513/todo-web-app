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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upcoming Tasks</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="wrapper">
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="header"><h3>Upcoming Tasks (Next 7 Days)</h3></div>

    <div class="card">
        <?php if ($tasks->num_rows > 0): ?>
            <?php while ($t = $tasks->fetch_assoc()): ?>
                <p>
                    <b><?php echo htmlspecialchars($t['title']); ?></b> —
                    Due: <?php echo $t['due_date']; ?> |
                    Priority: <?php echo $t['priority']; ?>
                </p>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No upcoming tasks.</p>
        <?php endif; ?>
    </div>
</div>
</div>
</body>
</html>
