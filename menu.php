<?php
require 'db.php';
require 'auth.php';

// Handle "Add to Cart" form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    // User must be logged in to add items to cart
    requireLogin();

    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die('Invalid request.');
    }

    $item_id = filter_var($_POST['item_id'] ?? '', FILTER_VALIDATE_INT);
    $qty     = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT, [
        'options' => ['default' => 1, 'min_range' => 1, 'max_range' => 10]
    ]);

    if (!$item_id || $qty < 1) {
        header("Location: menu.php?error=invalid");
        exit();
    }

    // Verify the item exists and is available
    $stmt = $pdo->prepare("SELECT id FROM menu_items WHERE id = ? AND available = 1");
    $stmt->execute([$item_id]);
    if (!$stmt->fetch()) {
        header("Location: menu.php?error=notfound");
        exit();
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id] += $qty;
    } else {
        $_SESSION['cart'][$item_id] = $qty;
    }

    header("Location: menu.php?added=1");
    exit();
}

// Fetch all menu items that are marked as available
$stmt  = $pdo->query("SELECT * FROM menu_items WHERE available = 1 ORDER BY category");
$items = $stmt->fetchAll();

// Group items by their category for easy display on the page
$menu = [];
foreach ($items as $item) {
    $menu[$item['category']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - ZimBites Restaurant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="menu-page">
        <h1>Our Menu</h1>
        <div style="margin: 1em 0; text-align: center;">
            <button onclick="window.history.back()" class="btn">&larr; Back</button>
        </div>

        <div style="margin: 1em 0; text-align: center;">
            <button onclick="window.history.back()" class="btn">&larr; Back</button>
        </div>

        <!-- Show confirmation or error banners -->
        <?php if (isset($_GET['added'])): ?>
            <div class="alert success">Item added to your cart!</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
            <div class="alert error">Invalid item or quantity selected.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'notfound'): ?>
            <div class="alert error">This item is no longer available.</div>
        <?php endif; ?>

        <!-- Loop through each category and display its items -->
        <?php foreach ($menu as $category => $items): ?>
        <section class="menu-category">
            <h2><?= htmlspecialchars($category) ?></h2>

            <div class="menu-grid">
                <?php foreach ($items as $item): ?>
                <div class="menu-card">
                    <div class="menu-card-body">
                        <!-- htmlspecialchars() prevents XSS when displaying DB data -->
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p><?= htmlspecialchars($item['description']) ?></p>
                        <span class="price">$<?= number_format($item['price'], 2) ?></span>
                    </div>

                    <?php if (isLoggedIn()): ?>
                    <!-- Show the add-to-cart form only for logged-in users -->
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <!-- Quantity selector, limited between 1 and 10 -->
                        <input type="number" name="quantity" value="1" min="1" max="10">
                        <button type="submit" name="add_to_cart">Add to Order</button>
                    </form>
                    <?php else: ?>
                        <!-- Guest users see a prompt to log in instead -->
                        <a href="login.php" class="btn">Login to Order</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endforeach; ?>
    </main>

    <div style="margin: 1em 0; text-align: center;">
        <button onclick="window.history.back()" class="btn">&larr; Back</button>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>