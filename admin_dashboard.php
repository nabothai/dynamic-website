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
if ($totalRevenue === null) $totalRevenue = 0;

// Total number of items on the menu
$totalMenuItems = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();

// Fetch the 5 most recent orders along with the customer's name
$recentOrders   = $pdo->query("
    SELECT o.*, u.name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll();

// Fetch all user messages for admin view
$messages = $pdo->query("SELECT * FROM messages ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ZimBites Restaurant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>


    <div style="margin: 1em 0; text-align: center;">
        <button onclick="window.history.back()" class="btn">&larr; Back</button>
    </div>
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
                <h3>$<?= is_numeric($totalRevenue) ? number_format($totalRevenue, 2) : '0.00' ?></h3>
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

        <!-- ===== USER MESSAGES TABLE ===== -->
        <h2>User Messages</h2>
        <?php if (empty($messages)): ?>
            <p>No messages have been sent yet.</p>
        <?php else: ?>
        <table class="data-table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
            </tr>
            <?php foreach ($messages as $msg): ?>
            <tr>
                <td><?= $msg['id'] ?></td>
                <td><?= htmlspecialchars($msg['name']) ?></td>
                <td><?= htmlspecialchars($msg['email']) ?></td>
                <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </main>

    <div style="margin: 1em 0; text-align: center;">
        <button onclick="window.history.back()" class="btn">&larr; Back</button>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
