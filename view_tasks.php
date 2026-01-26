<?php
session_start();
include 'db/config.php';
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM tasks WHERE user_id='$user_id' ORDER BY due_date ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Tasks</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Your Tasks</h2>
<table border="1">
<tr>
    <th>Title</th>
    <th>Description</th>
    <th>Category</th>
    <th>Due Date</th>
    <th>Status</th>
    <th>Action</th>
</tr>
<?php
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo "<tr>";
        echo "<td>".$row['title']."</td>";
        echo "<td>".$row['description']."</td>";
        echo "<td>".$row['category']."</td>";
        echo "<td>".$row['due_date']."</td>";
        echo "<td>".$row['status']."</td>";
        echo "<td><a href='edit_task.php?id=".$row['id']."'>Edit</a> | <a href='archive_task.php?id=".$row['id']."'>Archive</a></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No tasks found</td></tr>";
}
?>
</table>
</body>
</html>
