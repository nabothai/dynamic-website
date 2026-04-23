<?php
// Include database connection and session/auth helpers
require 'db.php';
require 'auth.php';

// Variables to store error and success messages shown to the user
$error   = '';
$success = '';

// Only process the form when the user submits it (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve and clean up form inputs
    // trim() removes extra spaces from the start and end
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // --- SERVER-SIDE VALIDATION ---

    // Check that no fields are empty
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";

    // Check that the email is a valid email format
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";

    // Enforce a minimum password length for security
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";

    // Make sure the two password fields match
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";

    } else {
        // Check if this email is already used by another account
        // We use a prepared statement with ? to prevent SQL injection
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            // Email already exists in the database
            $error = "Email already registered.";
        } else {
            // Hash the password before saving — NEVER store plain text passwords
            // PASSWORD_DEFAULT uses bcrypt, which is very secure
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashed]);

            $success = "Account created! <a href='login.php'>Login here</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bella Italia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; // Load the shared navigation bar ?>

    <div class="auth-container">
        <h2>Create Account</h2>

        <!-- Show error message if validation failed -->
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Show success message after successful registration -->
        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <!-- Registration form — posts back to this same page -->
        <form method="POST" action="">
            <!-- htmlspecialchars() prevents XSS by escaping special characters in repopulated values -->
            <input type="text"     name="name"             placeholder="Full Name"           value="<?= htmlspecialchars($_POST['name']  ?? '') ?>" required>
            <input type="email"    name="email"            placeholder="Email Address"        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <input type="password" name="password"         placeholder="Password (min 6 chars)"                                                      required>
            <input type="password" name="confirm_password" placeholder="Confirm Password"                                                             required>
            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>