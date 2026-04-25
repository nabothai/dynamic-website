<?php
require 'db.php';
require 'auth.php';

// Block access to this page if the user is not logged in
requireLogin();

$success = '';
$error = '';

// Handle cart updates and order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die('Invalid request.');
    }

    if (isset($_POST['update_quantity'], $_POST['item_id'], $_POST['quantity'])) {
        $item_id = filter_var($_POST['item_id'], FILTER_VALIDATE_INT);
        $qty     = filter_var($_POST['quantity'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 99]
        ]);

        if ($item_id && $qty && isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id] = $qty;
            $success = 'Cart quantities updated.';
        }
    }

    if (isset($_POST['remove_item'], $_POST['item_id'])) {
        $item_id = filter_var($_POST['item_id'], FILTER_VALIDATE_INT);
        if ($item_id && isset($_SESSION['cart'][$item_id])) {
            unset($_SESSION['cart'][$item_id]);
            $success = 'Item removed from your cart.';
        }
    }

    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        header('Location: dashboard.php');
        exit();
    }

    if (isset($_POST['place_order'])) {
        if (!empty($_SESSION['cart'])) {
            $total = 0;
            $ids = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $menuItems = [];

            foreach ($stmt->fetchAll() as $r) {
                $menuItems[$r['id']] = $r;
            }

            foreach ($_SESSION['cart'] as $id => $qty) {
                if (isset($menuItems[$id])) {
                    $total += $menuItems[$id]['price'] * $qty;
                }
            }

            if ($total > 0) {
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");
                $stmt->execute([$_SESSION['user_id'], $total]);
                $order_id = $pdo->lastInsertId();

                foreach ($_SESSION['cart'] as $item_id => $qty) {
                    if (isset($menuItems[$item_id])) {
                        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$order_id, $item_id, $qty, $menuItems[$item_id]['price']]);
                    }
                }

                $_SESSION['cart'] = [];
                $success = "Order placed successfully!";
            } else {
                $error = 'Unable to place your order. Please review your cart.';
            }
        } else {
            $error = 'Your cart is empty.';
        }
    }
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
    <title>My Dashboard - ZimBites Restaurant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="dashboard">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>

        <!-- Show order confirmation message if order was just placed -->
        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
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
                    <tr><th>Item</th><th>Qty</th><th>Price</th><th>Actions</th></tr>

                    <?php foreach ($cartItems as $ci):
                        $qty      = $_SESSION['cart'][$ci['id']];   // Quantity from the session
                        $subtotal = $ci['price'] * $qty;             // Price × quantity
                        $cartTotal += $subtotal;                      // Running total
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($ci['name']) ?></td>
                        <td>
                            <form method="POST" style="display:flex; gap:0.5rem; align-items:center;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="item_id" value="<?= $ci['id'] ?>">
                                <input type="number" name="quantity" value="<?= $qty ?>" min="1" max="99" style="width:70px; padding:0.3rem;">
                                <button type="submit" name="update_quantity" class="btn">Update</button>
                            </form>
                        </td>
                        <td>$<?= number_format($subtotal, 2) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="item_id" value="<?= $ci['id'] ?>">
                                <button type="submit" name="remove_item" class="btn btn-danger" style="padding:0.4rem 0.8rem;">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- Display the grand total row -->
                    <tr>
                        <td colspan="2"><strong>Total</strong></td>
                        <td><strong>$<?= number_format($cartTotal, 2) ?></strong></td>
                        <td></td>
                    </tr>
                </table>

                <div style="display:flex; flex-wrap:wrap; gap:0.75rem; margin-top:1rem;">
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                        <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
                    </form>

                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                        <button type="submit" name="clear_cart" class="btn btn-danger">Clear Cart</button>
                    </form>
                </div>
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

    <div style="margin: 1em 0; text-align: center;">
        <button onclick="window.history.back()" class="btn">&larr; Back</button>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>