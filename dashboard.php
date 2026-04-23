<?php
require 'db.php';
require 'auth.php';

// Block access to this page if the user is not logged in
requireLogin();

// Handle order placement when the user clicks "Place Order"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {

    // Only proceed if there are items in the cart
    if (!empty($_SESSION['cart'])) {
        $total = 0;

        // Get the IDs of all items currently in the cart
        $ids          = array_keys($_SESSION['cart']);

        // Build a string of ? placeholders for the IN clause (one per item)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Fetch the menu item details for all cart items in one query
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id IN ($placeholders)");
        $stmt->execute($ids);

        // Store items in an array keyed by their ID for easy lookup
        $menuItems = [];
        foreach ($stmt->fetchAll() as $r) {
            $menuItems[$r['id']] = $r;
        }

        // Calculate the total price of the order
        foreach ($_SESSION['cart'] as $id => $qty) {
            if (isset($menuItems[$id])) {
                $total += $menuItems[$id]['price'] * $qty;
            }
        }

        // Insert the order record into the orders table
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $total]);

        // Get the ID of the order we just inserted
        $order_id = $pdo->lastInsertId();

        // Insert each cart item as a row in the order_items table
        foreach ($_SESSION['cart'] as $item_id => $qty) {
            if (isset($menuItems[$item_id])) {
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item_id, $qty, $menuItems[$item_id]['price']]);
            }
        }

        // Empty the cart after the order is placed
        $_SESSION['cart'] = [];
        $success = "Order placed successfully!";
    }
}

// Handle the "Clear Cart" button
if (isset($_GET['clear_cart'])) {
    $_SESSION['cart'] = [];
    header("Location: dashboard.php");
    exit();
}

// Fetch all orders placed by this user, newest first
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Bella Italia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="dashboard">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>

        <!-- Show order confirmation message if order was just placed -->
        <?php if (isset($success)): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <!-- ===== CART SECTION ===== -->
        <section class="cart-section">
            <h2>🛒 Your Cart</h2>

            <?php if (empty($_SESSION['cart'])): ?>
                <!-- Cart is empty — prompt user to browse menu -->
                <p>Your cart is empty. <a href="menu.php">Browse the menu</a></p>

            <?php else: ?>
                <!-- Fetch item details for everything in the cart -->
                <?php
                $ids          = array_keys($_SESSION['cart']);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt         = $pdo->prepare("SELECT * FROM menu_items WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $cartItems    = $stmt->fetchAll();
                $cartTotal    = 0;
                ?>

                <table class="data-table">
                    <tr><th>Item</th><th>Qty</th><th>Price</th></tr>

                    <?php foreach ($cartItems as $ci):
                        $qty      = $_SESSION['cart'][$ci['id']];   // Quantity from the session
                        $subtotal = $ci['price'] * $qty;             // Price × quantity
                        $cartTotal += $subtotal;                      // Running total
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($ci['name']) ?></td>
                        <td><?= $qty ?></td>
                        <td>$<?= number_format($subtotal, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- Display the grand total row -->
                    <tr>
                        <td colspan="2"><strong>Total</strong></td>
                        <td><strong>$<?= number_format($cartTotal, 2) ?></strong></td>
                    </tr>
                </table>

                <!-- Place Order button submits the form to process the order -->
                <form method="POST">
                    <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
                </form>

                <!-- Clear Cart link removes all items from the session cart -->
                <a href="dashboard.php?clear_cart=1" class="btn btn-danger">Clear Cart</a>
            <?php endif; ?>
        </section>

        <!-- ===== ORDER HISTORY SECTION ===== -->
        <section class="orders-section">
            <h2>📦 My Orders</h2>

            <?php if (empty($orders)): ?>
                <p>No orders yet.</p>
            <?php else: ?>
                <table class="data-table">
                    <tr><th>Order #</th><th>Total</th><th>Status</th><th>Date</th></tr>

                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td>$<?= number_format($order['total_price'], 2) ?></td>

                        <!-- Status badge — CSS class changes color based on status value -->
                        <td>
                            <span class="status <?= $order['status'] ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>

                        <!-- Format the date into a readable format -->
                        <td><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>