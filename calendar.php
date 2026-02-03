<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Fetch tasks for calendar */
$stmt = $conn->prepare("
    SELECT id, title, due_date, status, priority
    FROM tasks
    WHERE user_id = ?
      AND is_archived = 0
      AND due_date IS NOT NULL
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        "id" => $row['id'],
        "title" => $row['title'],
        "start" => $row['due_date'],
        "color" => $row['status'] === 'Completed' ? '#9e9e9e' : (
            $row['priority'] === 'High' ? '#d32f2f' :
            ($row['priority'] === 'Medium' ? '#f9a825' : '#388e3c')
        )
    ];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Calendar | ToDo Student</title>

    <link rel="stylesheet" href="css/style.css">

    <!-- FullCalendar CDN -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    <style>
        #calendar {
            background: #ffffff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 1px 10px rgba(0,0,0,0.08);
        }

        body.dark-mode .header h3 {
            color: #ffffff;
        }

        body.dark-mode #calendar {
            background: #1e1e1e;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>

<body>
<div class="wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="header">
            <h3>Task Calendar</h3>
        </div>

        <div id="calendar"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },

        events: <?php echo json_encode($events); ?>,

        /* Click on a date → add new task */
        dateClick: function (info) {
            window.location.href = "add_task.php?date=" + info.dateStr;
        },

        /* Click on existing task → edit task */
        eventClick: function (info) {
            window.location.href = "edit_task.php?id=" + info.event.id;
        }
    });

    calendar.render();
});
</script>

</body>
</html>
