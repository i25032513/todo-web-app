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

// Handle password change
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $error = "New passwords do not match.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters.";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!password_verify($current, $row['password'])) {
            $error = "Current password is incorrect.";
        } else {
            // Update password
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $user_id);
            $stmt->execute();
            $stmt->close();

            $success = "Password changed successfully.";
        }
    }
}

// Handle account deletion
if (isset($_POST['delete_account'])) {
    // Delete user's tasks and sticky notes
    $conn->query("DELETE FROM tasks WHERE user_id = $user_id");
    $conn->query("DELETE FROM sticky_notes WHERE user_id = $user_id");

    // Delete the user account
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Logout and redirect to home page
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
<div class="wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="header">
            <h3>Settings</h3>
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

            <hr style="margin:25px 0;">

            <!-- Password Change Form -->
            <form method="post">
                <h4>Change Password</h4>

                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>

                <button type="submit" name="change_password" class="btn">Change Password</button>
            </form>

            <hr style="margin:25px 0;">

            <!-- Dark Mode Toggle Button -->
            <button type="button" onclick="toggleDarkMode()" class="btn">
                Toggle Dark Mode
            </button>

            <hr style="margin:25px 0;">

            <!-- Delete Account Form -->
            <form method="post" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                <button type="submit" name="delete_account" class="btn" style="background-color: #b91c1c; color: white;">
                    Delete Account
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Dark Mode Toggle Script -->
<script>
// Toggle dark mode
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    let darkModeStatus = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', darkModeStatus);
}

// Check localStorage for dark mode preference
window.onload = function() {
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
    }
};
</script>

</body>
</html>
