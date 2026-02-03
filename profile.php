<?php
session_start();
require 'db/config.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $gender = trim($_POST['gender']);

    $sql = "UPDATE users SET name = ?, email = ?, gender = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $name, $email, $gender, $user_id);
    $stmt->execute();

    $message = "Profile updated successfully.";

    // Refresh user data
    $user['name'] = $name;
    $user['email'] = $email;
    $user['gender'] = $gender;
}

// Dark mode
$dark_mode = $_SESSION['dark_mode'] ?? false;
?>

<!DOCTYPE html>
<html lang="en" <?= $dark_mode ? 'class="dark"' : '' ?>>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile</title>
<link rel="stylesheet" href="css/style.css">
<style>
body.dark { background-color: #121212; color: #fff; }

.profile-wrapper { max-width: 600px; margin: 40px auto; padding: 20px; }
.profile-card {
    background: var(--card-bg, #fff);
    border-radius: 12px;
    padding: 30px;
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s, box-shadow 0.2s;
}
.profile-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }

.profile-card h2 { font-size: 24px; margin-bottom: 20px; color: var(--primary-color); }
.profile-item { margin-bottom: 15px; font-size: 16px; }
.profile-item span { font-weight: bold; display: inline-block; width: 120px; }

input, select { width: calc(100% - 130px); padding: 6px 10px; border-radius: 6px; border: 1px solid #ccc; font-size: 15px; }
input[readonly], select[disabled] { background-color: #f4f4f4; }

.btn { padding: 10px 20px; border-radius: 8px; font-size: 14px; cursor: pointer; border: none; margin-right: 10px; }
.btn-primary { background: var(--primary-color); color: #fff; }
.btn-primary:hover { background: var(--primary-dark); }
.btn-secondary { background: #6c757d; color: #fff; }
.btn-secondary:hover { background: #5a6268; }

html.dark .btn-primary { background: var(--primary-color); color: #fff; }
html.dark .btn-primary:hover { background: var(--primary-dark); }
html.dark .btn-secondary { background: #6c757d; color: #fff; }
html.dark .btn-secondary:hover { background: #5a6268; }

.message { margin-bottom: 15px; color: green; font-size: 14px; }
</style>
</head>
<body>

<div class="wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="profile-wrapper">
            <div class="profile-card">
                <h2>My Profile</h2>

                <?php if($message) echo "<p class='message'>$message</p>"; ?>

                <form method="POST" id="profileForm">
                    <div class="profile-item">
                        <span>Name:</span>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                    </div>

                    <div class="profile-item">
                        <span>Email:</span>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                    </div>

                    <div class="profile-item">
                        <span>Gender:</span>
                        <select name="gender" disabled>
                            <option value="">Select</option>
                            <option value="Male" <?= $user['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= $user['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="profile-item">
                        <span>Registered On:</span>
                        <span><?= date('d M Y', strtotime($user['created_at'] ?? $user['register_date'] ?? '')) ?></span>
                    </div>

                    <div class="form-actions" style="margin-top:20px;">
                        <button type="button" class="btn btn-secondary" id="editBtn">Edit</button>
                        <button type="submit" name="update_profile" class="btn btn-primary" id="updateBtn" style="display:none;">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle Edit Mode
const editBtn = document.getElementById('editBtn');
const updateBtn = document.getElementById('updateBtn');
const form = document.getElementById('profileForm');

editBtn.addEventListener('click', () => {
    // Enable all inputs/selects
    form.querySelectorAll('input[name], select[name]').forEach(el => {
        el.removeAttribute('readonly');
        el.removeAttribute('disabled');
    });

    editBtn.style.display = 'none';
    updateBtn.style.display = 'inline-block';
});
</script>
</body>
</html>
