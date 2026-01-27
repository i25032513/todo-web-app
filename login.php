<?php
session_start();
include 'db/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$info = "";

// ✅ Show success message after registration
if (isset($_GET['registered']) && $_GET['registered'] == "1") {
    $info = "Registration successful! Please login.";
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // ✅ Set session for dashboard + sidebar
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_name'] = $user['name'];

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found. Please register first.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ToDo Student | Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
<div class="wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="header">
            <h3>Login to Your Account</h3>
        </div>

        <div class="card auth-card">
            <?php if ($info): ?>
                <p style="color: green; margin-bottom: 15px;"><?php echo htmlspecialchars($info); ?></p>
            <?php endif; ?>

            <?php if ($error): ?>
                <p style="color: red; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" name="login" class="btn" style="width: 100%;">Login</button>
            </form>

            <p style="margin-top: 15px; font-size: 14px;">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
