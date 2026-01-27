<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Fetch user info
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if ($name === "" || $email === "") {
        $error = "Name and email cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists (except for current user)
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Update user profile
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['user_name'] = $name;
            $success = "Profile updated successfully.";
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
<div class="wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="header">
            <h3>My Profile</h3>
        </div>

        <div class="card auth-card">
            <?php if ($error): ?>
                <p style="color: red; margin-bottom: 15px;"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p style="color: green; margin-bottom: 15px;"><?php echo $success; ?></p>
            <?php endif; ?>

            <!-- Profile Update Form -->
            <form method="post">
                <h4>Profile Information</h4>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>

                <button type="submit" name="update_profile" class="btn">Update Profile</button>
            </form>

            <p style="margin-top: 15px;">
                <a href="settings.php">Go to Settings</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
