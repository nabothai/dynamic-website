<?php
require 'db.php';
require 'auth.php';
requireAdmin();

// Fetch all orders with user info
$orders = $pdo->query("SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders - Bella Italia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="admin-panel">
        <h1>Manage Orders</h1>
        <table class="data-table">
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($orders as $order): ?>
            <tr>
                <form method="POST" action="manage_orders.php" style="display:contents;">
                <td><?= $order['id'] ?><input type="hidden" name="id" value="<?= $order['id'] ?>"></td>
                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                <td>$<?= number_format($order['total_price'], 2) ?></td>
                <td>
                    <select name="status">
                        <option value="pending" <?= $order['status']==='pending'?'selected':'' ?>>pending</option>
                        <option value="processing" <?= $order['status']==='processing'?'selected':'' ?>>processing</option>
                        <option value="completed" <?= $order['status']==='completed'?'selected':'' ?>>completed</option>
                        <option value="cancelled" <?= $order['status']==='cancelled'?'selected':'' ?>>cancelled</option>
                    </select>
                </td>
                <td><?= $order['created_at'] ?></td>
                <td>
                    <button type="submit" name="update_order" class="btn btn-primary" style="padding:0.3em 0.7em;">Update</button>
                </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php
    // Handle order status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
        $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->execute([
            $_POST['status'],
            $_POST['id']
        ]);
        header('Location: manage_orders.php');
        exit();
    }
    ?>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
