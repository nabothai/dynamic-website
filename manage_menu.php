<?php
require 'db.php';
require 'auth.php';

// Only admins can access this page
requireAdmin();

$msg = '';

// ===== CREATE — Add a new menu item =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    // Sanitize inputs with trim() to remove accidental whitespace
    $name  = trim($_POST['name']);
    $desc  = trim($_POST['description']);
    $price = (float)$_POST['price'];  // Cast to float to ensure it's a number
    $cat   = trim($_POST['category']);

    // Basic validation before inserting
    if ($name && $price > 0) {
        // Prepared statement prevents SQL injection
        $stmt = $pdo->prepare("INSERT INTO menu_items (name, description, price, category) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $desc, $price, $cat]);
        $msg = "Item added successfully!";
    }
}

// ===== UPDATE — Edit an existing menu item =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item'])) {
    $stmt = $pdo->prepare("
        UPDATE menu_items
        SET name=?, description=?, price=?, category=?, available=?
        WHERE id=?
    ");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['category'],
        isset($_POST['available']) ? 1 : 0, // Checkbox: 1 if checked, 0 if not
        $_POST['id']                         // ID of the item to update
    ]);
    $msg = "Item updated!";
}

// ===== DELETE — Remove a menu item =====
if (isset($_GET['delete'])) {
    // Cast to int to prevent SQL injection via the URL parameter
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);

    // Redirect to avoid re-running the delete on page refresh
    header("Location: manage_menu.php?msg=deleted");
    exit();
}

// Fetch all menu items ordered by category for display
$items = $pdo->query("SELECT * FROM menu_items ORDER BY category")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Menu - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="admin-panel">
        <h1>🍕 Manage Menu Items</h1>

        <!-- Show feedback message after any CRUD action -->
        <?php if ($msg): ?>
            <div class="alert success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- ===== ADD NEW ITEM FORM ===== -->
        <section class="form-section">
            <h2>Add New Item</h2>
            <form method="POST">
                <input type="text"   name="name"        placeholder="Item Name"                    required>
                <textarea            name="description"  placeholder="Description"></textarea>
                <input type="number" name="price"        placeholder="Price" step="0.01" min="0"   required>
                <input type="text"   name="category"     placeholder="Category (e.g. Pizza, Pasta)">
                <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
            </form>
        </section>

        <!-- ===== ALL MENU ITEMS TABLE ===== -->
        <h2>All Menu Items</h2>
        <table class="data-table">
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['category']) ?></td>
                <td>$<?= number_format($item['price'], 2) ?></td>
                <!-- Show tick or cross based on whether the item is available -->
                <td><?= $item['available'] ? '✅' : '❌' ?></td>
                <td>
                    <!-- Inline edit form — each row has its own update form -->
                    <form method="POST" style="display:inline">
                        <!-- Hidden field sends the item ID so we know which row to update -->
                        <input type="hidden" name="id"          value="<?= $item['id'] ?>">
                        <input type="text"   name="name"        value="<?= htmlspecialchars($item['name']) ?>"        required>
                        <input type="number" name="price"       value="<?= $item['price'] ?>"                         step="0.01">
                        <input type="text"   name="category"    value="<?= htmlspecialchars($item['category']) ?>">
                        <input type="text"   name="description" value="<?= htmlspecialchars($item['description']) ?>">

                        <!-- Checkbox to toggle availability -->
                        <label>
                            <input type="checkbox" name="available" <?= $item['available'] ? 'checked' : '' ?>>
                            Available
                        </label>

                        <button type="submit" name="update_item" class="btn">Update</button>
                    </form>

                    <!-- Delete link — passes the item ID in the URL as a GET parameter -->
                    <!-- onclick confirm() asks the user before deleting -->
                    <a href="manage_menu.php?delete=<?= $item['id'] ?>"
                       class="btn btn-danger"
                       onclick="return confirm('Delete this item?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>