<?php
require 'db.php';
require 'auth.php';
requireAdmin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die('Invalid request.');
    }

    if (isset($_POST['update_user'])) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'] ?? 'user';

        if (!$id) {
            $error = 'Invalid user.';
        } elseif (!$name) {
            $error = 'Name is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif (!in_array($role, ['user', 'admin'], true)) {
            $error = 'Invalid role selected.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
            $stmt->execute([$name, $email, $role, $id]);
            $success = 'User updated.';
            header('Location: manage_users.php?updated=1');
            exit();
        }
    }

    if (isset($_POST['delete_user'])) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id && $id !== $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
            $stmt->execute([$id]);
            $success = 'User deleted.';
            header('Location: manage_users.php?deleted=1');
            exit();
        }
        $error = 'Cannot delete the logged-in admin account.';
    }
}

// Fetch all users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - ZimBites Restaurant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div style="margin: 1em 0; text-align: center;">
        <button onclick="window.history.back()" class="btn">&larr; Back</button>
    </div>
    <main class="admin-panel">
        <h1>Manage Users</h1>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert success">User updated.</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert success">User deleted.</div>
        <?php elseif ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <table class="data-table">
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <form method="POST" action="manage_users.php" style="display:contents;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <td><?= $user['id'] ?><input type="hidden" name="id" value="<?= $user['id'] ?>"></td>
                <td><input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" style="width:120px"></td>
                <td><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" style="width:180px"></td>
                <td>
                    <select name="role">
                        <option value="user" <?= $user['role']==='user'?'selected':'' ?>>user</option>
                        <option value="admin" <?= $user['role']==='admin'?'selected':'' ?>>admin</option>
                    </select>
                </td>
                <td><?= $user['created_at'] ?></td>
                <td>
                    <button type="submit" name="update_user" class="btn btn-primary" style="padding:0.3em 0.7em;">Update</button>
                    <button type="submit" name="delete_user" class="btn btn-danger" style="padding:0.3em 0.7em;" onclick="return confirm('Delete this user?')">Delete</button>
                </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php
    // Handle user update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['role'],
            $_POST['id']
        ]);
        header('Location: manage_users.php');
        exit();
    }
    // Handle user delete
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$_POST['id']]);
        header('Location: manage_users.php');
        exit();
    }
    ?>
    </main>
    <div style="margin: 1em 0; text-align: center;">
        <button onclick="window.history.back()" class="btn">&larr; Back</button>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
