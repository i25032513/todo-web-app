<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$priorities = ['High', 'Medium', 'Low'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Priority View</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="wrapper">
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="header"><h3>Tasks by Priority</h3></div>

    <?php foreach ($priorities as $p): 
        $stmt = $conn->prepare("
            SELECT * FROM tasks
            WHERE user_id = ? AND priority = ? AND is_archived = 0
        ");
        $stmt->bind_param("is", $user_id, $p);
        $stmt->execute();
        $tasks = $stmt->get_result();
    ?>
        <div class="card">
            <h4><?php echo $p; ?> Priority</h4>
            <?php if ($tasks->num_rows > 0): ?>
                <?php while ($t = $tasks->fetch_assoc()): ?>
                    <p><?php echo htmlspecialchars($t['title']); ?> (<?php echo $t['status']; ?>)</p>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No tasks.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</div>
</body>
</html>
