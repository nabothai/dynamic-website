<?php
require 'db.php';
require 'auth.php';

$success = '';
$error = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die('Invalid request.');
    }

    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Validation
    if (empty($name) || empty($email) || empty($message)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        // Insert message into database
        $stmt = $pdo->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        $success = "Thank you! Your message has been sent. We'll get back to you soon.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - ZimBites Restaurant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>


    <main class="admin-panel" style="max-width:600px;">
        <h1 style="text-align:center; color:#c0392b; margin-bottom:1.5em;">Contact Us</h1>
        <div style="margin: 1em 0; text-align: center;">
            <button onclick="window.history.back()" class="btn">&larr; Back</button>
        </div>

        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="" style="background:white; padding:2em; border-radius:12px; box-shadow:0 2px 10px #0001;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <div style="margin-bottom:1.2em;">
                <label style="display:block; margin-bottom:0.5em; font-weight:500;">Your Name</label>
                <input type="text" name="name" placeholder="Enter your full name" required 
                    style="width:100%; padding:0.8em; border:1px solid #ddd; border-radius:6px; font-size:1em;">
            </div>

            <div style="margin-bottom:1.2em;">
                <label style="display:block; margin-bottom:0.5em; font-weight:500;">Your Email</label>
                <input type="email" name="email" placeholder="Enter your email address" required 
                    style="width:100%; padding:0.8em; border:1px solid #ddd; border-radius:6px; font-size:1em;">
            </div>

            <div style="margin-bottom:1.2em;">
                <label style="display:block; margin-bottom:0.5em; font-weight:500;">Message</label>
                <textarea name="message" placeholder="Enter your message or feedback..." required rows="6"
                    style="width:100%; padding:0.8em; border:1px solid #ddd; border-radius:6px; font-size:1em; resize:vertical;"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; padding:0.8em; font-size:1.1em;">Send Message</button>
        </form>

        <div style="text-align:center; margin-top:2em; color:#666;">
            <div style="margin-bottom:0.7em; font-weight:600; color:#c0392b;">Contact Details</div>
            <div>Cell: 0778629214</div>
            <div>Address: 4656 CausewayRoad Mutare</div>
            <div style="margin-top:1em;">We value your feedback and will respond as soon as possible.</div>
        </div>
        </main>

        <div style="margin: 1em 0; text-align: center;">
            <button onclick="window.history.back()" class="btn">&larr; Back</button>
        </div>
    </main>

    <div style="margin: 1em 0; text-align: center;">
        <button onclick="window.history.back()" class="btn">&larr; Back</button>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
