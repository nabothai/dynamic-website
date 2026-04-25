<?php
require 'db.php';
require 'auth.php';
requireAdmin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die('Invalid request.');
    }

    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';
    $allowed = ['pending', 'processing', 'completed', 'cancelled'];

    if (!$id || !in_array($status, $allowed, true)) {
        $error = 'Invalid order update.';
    } else {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        header('Location: manage_orders.php?updated=1');
        exit();
    }
}

// Fetch orders
$orders = $pdo->query("
    SELECT o.*, u.name AS customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Orders - ZimBites Restaurant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>


<div style="margin: 1em 0; text-align: center;">
    <button onclick="window.history.back()" class="btn">&larr; Back</button>
</div>
<main class="admin-panel">
    <h1>Manage Orders</h1>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert success">Order status updated.</div>
    <?php elseif ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table class="data-table">
        <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>

        <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= $order['id'] ?></td>
            <td><?= htmlspecialchars($order['customer_name']) ?></td>
            <td>$<?= number_format($order['total_price'], 2) ?></td>

            <td>
                <form method="POST" action="manage_orders.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= $order['id'] ?>">

                    <select name="status">
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
            </td>

            <td><?= $order['created_at'] ?></td>

            <td>
                    <button type="submit" name="update_order" class="btn btn-primary">
                        Update
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</main>

<div style="margin: 1em 0; text-align: center;">
    <button onclick="window.history.back()" class="btn">&larr; Back</button>
</div>
<?php include 'footer.php'; ?>

</body>
</html>