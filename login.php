<?php
// login.php
include('config.php');

$error = '';

if (isset($_POST['login'])) {
    // Simple sanitization—remember to use more robust methods in production.
    $username = $conn->real_escape_string($_POST['username']);
    // Here we use md5 for simplicity. In production use password_hash and password_verify.
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if($result && $result->num_rows === 1){
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS System - Login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .login-container { width: 300px; margin: auto; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if ($error) { echo "<p class='error'>$error</p>"; } ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" name="login" value="Login">
        </form>
    </div>
</body>
</html>
