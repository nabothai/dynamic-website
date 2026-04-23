<?php
require 'db.php';
require 'auth.php';
requireAdmin();

// Fetch all users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Bella Italia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="admin-panel">
        <h1>Manage Users</h1>
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
    <?php include 'footer.php'; ?>
</body>
</html>
