<?php
require 'db.php';
require 'auth.php';

$error = '';

// Only run login logic when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic check — make sure neither field is empty
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Look up the user by email using a prepared statement (prevents SQL injection)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(); // Returns the user row or false if not found

        // password_verify() compares the plain text input against the stored hash
        if ($user && password_verify($password, $user['password'])) {

            // Store user info in the session so we can access it on other pages
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            // Send admin users to the admin panel, regular users to their dashboard
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit(); // Stop script execution after redirect

        } else {
            // Keep the error message vague — don't reveal whether email or password is wrong
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bella Italia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="auth-container">
        <h2>Welcome Back</h2>

        <!-- Show error message if login failed -->
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off" class="login-form" style="max-width:350px;margin:2em auto;padding:2em;background:#fff;border-radius:10px;box-shadow:0 2px 8px #0001;display:flex;flex-direction:column;gap:1.2em;">
            <input type="email" name="email" placeholder="Email Address" required autocomplete="off" style="padding:0.8em 1em;border-radius:6px;border:1px solid #ccc;font-size:1em;">
            <input type="password" name="password" placeholder="Password" required autocomplete="off" style="padding:0.8em 1em;border-radius:6px;border:1px solid #ccc;font-size:1em;">
            <button type="submit" style="padding:0.8em 1em;border:none;border-radius:6px;background:#c0392b;color:#fff;font-size:1.1em;cursor:pointer;transition:background 0.2s;">Login</button>
        </form>

        <p style="text-align:center;margin-top:1em;">No account? <a href="register.php">Register here</a></p>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>