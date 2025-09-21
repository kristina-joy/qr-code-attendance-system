<?php
session_start();

// Database connection using PDO
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "qr_attendance_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connection Successful"; // optional
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: events.php');
    exit;
}

// Login logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user from database
    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Plain-text password check (no hash)
    if ($user && $password === $user['password']) {
        $_SESSION['user'] = $user['username'];
        header('Location: events.php');
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { 
    background: #193368 url('BLUE.jpg') no-repeat center center; 
    background-size: cover; 
    display:flex; justify-content:center; align-items:center; height:100vh;
}
.login-container { 
    width:100%; max-width:400px; padding:30px; background:rgba(255,255,255,0.95); 
    border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.3);
}
.logo { display:block; margin:0 auto 20px; width:120px; }
.error-msg { color:red; text-align:center; margin-bottom:15px; }
.btn-primary { width:100%; font-weight:bold; background-color:#193368; border:none; }
.btn-primary:hover { background-color:#0f264c; }
</style>
</head>
<body>

<div class="login-container">
    <img src="logo.png" class="logo" alt="Logo">
    <h3 class="text-center mb-4">Login</h3>

    <?php if(isset($error)) echo "<div class='error-msg'>$error</div>"; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" class="form-control" name="username" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" class="form-control" name="password" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="rememberMe">
            <label class="form-check-label">Remember me</label>
        </div>
        <button type="button" class="btn btn-primary" onclick="this.closest('form').submit();">
    Login
</button>

    </form>

    <p class="mt-3 text-center text-muted">Use your database login: <strong>admin / admin123</strong></p>
</div>

</body>
</html>


