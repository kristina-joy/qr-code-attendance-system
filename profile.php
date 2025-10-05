<?php
session_start();
include('./conn/conn.php');

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['user'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['username']);
    
    if ($new_name) {
        $stmt = $conn->prepare("UPDATE tbl_users SET username = :username WHERE username = :user");
        $stmt->execute([
            ':username' => $new_name,
            ':user' => $user
        ]);
        $_SESSION['user'] = $new_name; // update session
        $success = "Profile updated successfully!";
    } else {
        $error = "Name cannot be empty.";
    }
}
?>
<form method="POST">
    <?php if($success) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
    <label>Username:</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user); ?>" required>
    <button type="submit">Save Changes</button>
</form>
