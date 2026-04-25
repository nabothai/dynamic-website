<?php
require 'db.php';
require 'auth.php';
requireAdmin();

// Fetch all messages
$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();

// Handle message delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: manage_messages.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - ZimBites Restaurant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="admin-panel">
        <h1>📧 Contact Messages</h1>
        <p style="color:#666; margin-bottom:1.5em;">Total messages: <strong><?= count($messages) ?></strong></p>

        <?php if (empty($messages)): ?>
            <div style="text-align:center; padding:2em; background:#f5f5f5; border-radius:8px;">
                <p style="color:#999; font-size:1.1em;">No messages yet.</p>
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:1.2em;">
                <?php foreach ($messages as $msg): ?>
                <div style="background:white; padding:1.5em; border-radius:10px; border-left:4px solid #27ae60; box-shadow:0 2px 8px #0001;">
                    <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:0.8em;">
                        <div>
                            <h3 style="color:#c0392b; margin-bottom:0.3em;"><?= htmlspecialchars($msg['name']) ?></h3>
                            <p style="color:#666; font-size:0.9em;">
                                <strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" style="color:#27ae60;"><?= htmlspecialchars($msg['email']) ?></a>
                            </p>
                        </div>
                        <div style="text-align:right; white-space:nowrap;">
                            <a href="manage_messages.php?delete=<?= $msg['id'] ?>" class="btn btn-danger" 
                               style="padding:0.4em 0.8em; font-size:0.9em;" onclick="return confirm('Delete this message?')">Delete</a>
                        </div>
                    </div>

                    <div style="background:#f9f9f9; padding:1em; border-radius:6px; margin-bottom:0.8em;">
                        <p style="color:#333; line-height:1.6;"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                    </div>

                    <p style="color:#999; font-size:0.85em; text-align:right;">
                        📅 <?= date('d M Y, H:i', strtotime($msg['created_at'])) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <div style="margin: 1em 0; text-align: center;">
        <button onclick="window.history.back()" class="btn">&larr; Back</button>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
