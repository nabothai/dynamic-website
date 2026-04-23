<?php
require 'db.php';
require 'auth.php';

// Handle "Add to Cart" form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    // User must be logged in to add items to cart
    requireLogin();

    // Cast to int to prevent any injection through the item_id field
    $item_id = (int)$_POST['item_id'];
    $qty     = (int)$_POST['quantity'];

    // Initialize the cart array in the session if it doesn't exist yet
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    // If the item is already in the cart, increase its quantity
    // Otherwise, add it as a new entry
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id] += $qty;
    } else {
        $_SESSION['cart'][$item_id] = $qty;
    }

    // Redirect to avoid resubmitting the form on page refresh
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
    <title>Menu - Bella Italia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="menu-page">
        <h1>Our Menu</h1>

        <!-- Show confirmation banner when an item is added to cart -->
        <?php if (isset($_GET['added'])): ?>
            <div class="alert success">Item added to your cart!</div>
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
                        <!-- Hidden field sends the item ID with the form -->
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

    <?php include 'footer.php'; ?>
</body>
</html>