<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$start = date('Y-m-d', strtotime('monday this week'));
$end = date('Y-m-d', strtotime('sunday this week'));

$stmt = $conn->prepare("
    SELECT * FROM tasks
    WHERE user_id = ?
      AND due_date BETWEEN ? AND ?
      AND is_archived = 0
");
$stmt->bind_param("iss", $user_id, $start, $end);
$stmt->execute();
$tasks = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Weekly Overview</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="wrapper">
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="header"><h3>This Week’s Tasks</h3></div>

    <div class="card">
        <?php while ($t = $tasks->fetch_assoc()): ?>
            <p>
                <?php echo $t['due_date']; ?> —
                <b><?php echo htmlspecialchars($t['title']); ?></b>
                (<?php echo $t['status']; ?>)
            </p>
        <?php endwhile; ?>
    </div>
</div>
</div>
</body>
</html>
