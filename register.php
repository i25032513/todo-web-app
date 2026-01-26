<?php
include 'db/config.php';

if(isset($_POST['submit'])){
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username,email,password) VALUES ('$username','$email','$password')";
    if($conn->query($sql) === TRUE){
        $success = "Registration successful!";
    } else {
        $error = $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Register</h2>
<?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post" action="">
    Username: <input type="text" name="username" required><br>
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" name="submit" value="Register">
</form>
</body>
</html>
