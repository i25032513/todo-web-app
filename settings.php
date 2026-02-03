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

// Handle form submissions
$message_password = '';
$error_password = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Password update
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password_raw = $_POST['new_password'];

        if (empty($new_password_raw)) {
            $error_password = "New password cannot be empty.";
        } elseif (!password_verify($current_password, $user['password'])) {
            $error_password = "Current password is incorrect.";
        } else {
            $new_password = password_hash($new_password_raw, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $new_password, $user_id);
            $stmt->execute();
            $message_password = "Password updated successfully.";
        }

    // Delete account
    } elseif (isset($_POST['delete_account'])) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        session_destroy();
        header('Location: register.php');
        exit;

    // Dark mode toggle
    } elseif (isset($_POST['toggle_mode'])) {
        $_SESSION['dark_mode'] = isset($_POST['dark_mode']) && $_POST['dark_mode'] === '1';
    }
}

// Dark mode state
$dark_mode = $_SESSION['dark_mode'] ?? false;
?>

<!DOCTYPE html>
<html lang="en" <?= $dark_mode ? 'class="dark-mode"' : '' ?>>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings</title>
<link rel="stylesheet" href="css/style.css">
<style>
body.dark-mode { background-color: #121212; color: #fff; }

/* Toggle switch */
.switch { position: relative; display: inline-block; width: 60px; height: 34px; }
.switch input { display:none; }
.slider {
  position: absolute; cursor: pointer; top:0; left:0; right:0; bottom:0;
  background-color: #ccc; transition: .4s; border-radius: 34px;
}
.slider:before {
  position: absolute; content: ""; height:26px; width:26px; left:4px; bottom:4px;
  background-color:white; transition:.4s; border-radius:50%;
}
input:checked + .slider { background-color: var(--primary-color, #007bff); }
input:checked + .slider:before { transform: translateX(26px); }
.slider.round { border-radius:34px; }
.slider.round:before { border-radius:50%; }

/* Cards */
.settings-wrapper { max-width: 900px; margin: 0 auto; padding: 20px; }
.settings-wrapper .card { margin-bottom: 30px; padding: 20px; background: var(--card-bg, #fff); border-radius: 12px; box-shadow: var(--shadow-sm); }
body.dark-mode .card { background: #1e1e1e; }

h4 { margin-bottom: 15px; color: var(--primary-color); }
.btn { padding: 10px 20px; border-radius: 8px; font-size: 14px; cursor: pointer; border: none; margin-top: 10px; background: var(--primary-color, #007bff); color: #fff; }
.btn:hover { opacity: 0.9; }

.btn.btn-danger { background: var(--danger-color, #dc3545); color: #fff; }
.btn.btn-danger:hover { background: #c9302c; }

body.dark-mode .btn { background: var(--primary-color, #007bff); color: #fff; }
body.dark-mode .btn:hover { opacity: 0.9; }
body.dark-mode .btn.btn-danger { background: var(--danger-color, #dc3545); color: #fff; }
body.dark-mode .btn.btn-danger:hover { background: #c9302c; }
body.dark-mode .btn.btn-secondary { background: #e9ecef; color: var(--text-color); }
body.dark-mode .btn.btn-secondary:hover { background: #dfe3e6; }

input[type="password"] { width: 100%; padding: 8px; margin-bottom: 10px; border-radius:6px; border:1px solid #ccc; }
.form-group { margin-bottom: 15px; }

p.message { color: green; font-size: 14px; margin-bottom:10px; }
p.error { color: red; font-size: 14px; margin-bottom:10px; }

/* Modal styles */
#deleteModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center; }
#deleteModal div { background:#fff; padding:30px; border-radius:12px; max-width:400px; width:90%; text-align:center; position:relative; }
body.dark-mode #deleteModal div { background:#2a2a2a; color:#fff; }
</style>
</head>
<body>
<div class="wrapper">
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="header"><h3>Settings</h3></div>
    <div class="settings-wrapper">

        <!-- Password Section (always visible) -->
        <div class="card auth-card">
            <h4>Change Password</h4>
            <?php 
            if ($message_password) echo "<p class='message'>$message_password</p>";
            if ($error_password) echo "<p class='error'>$error_password</p>";
            ?>
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password">
                </div>

                <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
                    <input type="checkbox" id="show_password_checkbox" style="width:16px; height:16px; cursor:pointer;">
                    <label for="show_password_checkbox" style="cursor:pointer; margin:0; font-size:14px;">Show Password</label>
                </div>

                <button type="submit" name="update_password" class="btn">Update Password</button>
            </form>
        </div>

        <!-- Dark Mode -->
        <div class="card auth-card">
            <h4>Dark Mode</h4>
            <form method="POST">
                <label class="switch">
                    <input type="checkbox" name="dark_mode" value="1" <?= $dark_mode ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="slider round"></span>
                </label>
                <p style="margin-top:10px; font-size:14px;">Dark mode is <?= $dark_mode ? 'ON' : 'OFF' ?></p>
                <input type="hidden" name="toggle_mode">
            </form>
        </div>

        <!-- Delete Account -->
        <div class="card auth-card">
            <h4>Delete Account</h4>
            <p style="color: var(--danger-color, #dc3545); font-size:14px; margin-bottom:15px;">
                Warning: This action cannot be undone.
            </p>
            <button type="button" class="btn btn-danger" id="deleteAccountBtn">Delete Account</button>
        </div>

        <!-- Custom Delete Modal -->
        <div id="deleteModal">
            <div>
                <h4 style="margin-bottom:20px; color:#dc3545;">Confirm Deletion</h4>
                <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                <div style="margin-top:20px; display:flex; justify-content:center; gap:10px;">
                    <form method="POST" style="margin:0;">
                        <button type="submit" name="delete_account" class="btn btn-danger">Yes, Delete</button>
                    </form>
                    <button type="button" id="cancelDelete" class="btn btn-secondary">Cancel</button>
                </div>
            </div>
        </div>

    </div>
</div>
</div>

<script>
// Show password toggle
const currentInput = document.getElementById('current_password');
const newInput = document.getElementById('new_password');
const showCheckbox = document.getElementById('show_password_checkbox');
showCheckbox.addEventListener('change', () => {
    const type = showCheckbox.checked ? 'text' : 'password';
    currentInput.type = type;
    newInput.type = type;
});

// Delete account modal
const deleteBtn = document.getElementById('deleteAccountBtn');
const deleteModal = document.getElementById('deleteModal');
const cancelBtn = document.getElementById('cancelDelete');

deleteBtn.addEventListener('click', () => {
    deleteModal.style.display = 'flex';
});

cancelBtn.addEventListener('click', () => {
    deleteModal.style.display = 'none';
});

window.addEventListener('click', (e) => {
    if (e.target === deleteModal) {
        deleteModal.style.display = 'none';
    }
});
</script>
</body>
</html>
