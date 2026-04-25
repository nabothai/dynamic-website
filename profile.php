<?php
require 'db.php';
require 'auth.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die('Invalid request.');
    }
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];

    if (!$name || !$email) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($password && strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password && $password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email is used by another user
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $error = 'Email already in use.';
        } else {
            if ($password) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET name=?, email=?, password=? WHERE id=?');
                $stmt->execute([$name, $email, $hashed, $user_id]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name=?, email=? WHERE id=?');
                $stmt->execute([$name, $email, $user_id]);
            }
            $_SESSION['name'] = $name;
            $success = 'Profile updated.';
        }
    }
}

// Fetch current user info
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - ZimBites Restaurant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<main class="auth-container">
    <h2>Edit Your Details</h2>
    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" placeholder="Full Name" required>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
        <input type="password" name="confirm_password" placeholder="Confirm New Password">
        <button type="submit" name="update_profile">Update Profile</button>
    </form>
</main>
<div style="margin: 1em 0; text-align: center;">
    <button onclick="window.history.back()" class="btn">&larr; Back</button>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
