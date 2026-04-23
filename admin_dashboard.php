<?php
require 'db.php';
require 'auth.php';

// Block anyone who is not an admin from accessing this page
requireAdmin();

// --- DASHBOARD STATISTICS ---
// fetchColumn() returns just the first column of the first row (the count)

// Total number of regular users (excluding admins)
$totalUsers     = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();

// Total number of orders ever placed
$totalOrders    = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Total revenue from all non-cancelled orders
$totalRevenue   = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'")->fetchColumn();

// Total number of items on the menu
$totalMenuItems = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();

// Fetch the 5 most recent orders along with the customer's name
// JOIN connects the orders table to users so we get the customer name
$recentOrders   = $pdo->query("
    SELECT o.*, u.name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bella Italia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="admin-panel">
        <h1>⚙️ Admin Dashboard</h1>

        <!-- ===== STATS CARDS ===== -->
        <!-- Each card shows one key metric for the business -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $totalUsers ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h3><?= $totalOrders ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <!-- number_format() shows 2 decimal places for currency -->
                <h3>$<?= number_format($totalRevenue, 2) ?></h3>
                <p>Total Revenue</p>
            </div>
            <div class="stat-card">
                <h3><?= $totalMenuItems ?></h3>
                <p>Menu Items</p>
            </div>
        </div>

        <!-- Admin quick links -->
        <div style="margin: 2em 0; display: flex; gap: 1em;">
            <a href="manage_menu.php" class="btn btn-primary">🍕 Manage Menu</a>
            <a href="manage_orders.php" class="btn btn-primary">📦 Manage Orders</a>
            <a href="manage_users.php" class="btn btn-primary">👥 Manage Users</a>
        </div>

        <!-- ===== RECENT ORDERS TABLE ===== -->
        <h2>Recent Orders</h2>
        <table class="data-table">
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            <?php foreach ($recentOrders as $order): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['name']) ?></td>
                <td>$<?= number_format($order['total_price'], 2) ?></td>
                <td><?= htmlspecialchars($order['status']) ?></td>
                <td><?= $order['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
