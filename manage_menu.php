<?php
require 'db.php';
require 'auth.php';

// Only admins can access this page
requireAdmin();

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die('Invalid request.');
    }

    // Handle image upload for add and update
    function handleImageUpload($inputName, $oldFile = null) {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
            <div style="margin: 1em 0; text-align: center;">
                <button onclick="window.history.back()" class="btn">&larr; Back</button>
            </div>
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            $type = mime_content_type($_FILES[$inputName]['tmp_name']);
            if (!isset($allowed[$type])) return $oldFile;
            $ext = $allowed[$type];
            $filename = uniqid('dish_', true) . '.' . $ext;
            $dest = __DIR__ . '/images/' . $filename;
            if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $dest)) {
                // Optionally delete old file
                if ($oldFile && file_exists(__DIR__ . '/images/' . $oldFile)) {
                    @unlink(__DIR__ . '/images/' . $oldFile);
                }
                return $filename;
            }
        }
        return $oldFile;
    }

    if (isset($_POST['add_item'])) {
        $name  = trim($_POST['name']);
        $desc  = trim($_POST['description']);
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $cat   = trim($_POST['category']);
        $img   = handleImageUpload('image');

        if (!$name) {
            $error = 'Item name is required.';
        } elseif ($price === false || $price <= 0) {
            $error = 'Please enter a valid price greater than 0.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO menu_items (name, description, price, category, available, image) VALUES (?, ?, ?, ?, 1, ?)");
            $stmt->execute([$name, $desc, $price, $cat, $img]);
            $msg = "Item added successfully!";
        }
    }

    if (isset($_POST['update_item'])) {
        $id    = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $name  = trim($_POST['name']);
        $desc  = trim($_POST['description']);
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $cat   = trim($_POST['category']);
        $avail = isset($_POST['available']) ? 1 : 0;
        $oldImg = $_POST['old_image'] ?? null;
        $img   = handleImageUpload('image', $oldImg);

        if (!$id) {
            $error = 'Invalid menu item.';
        } elseif (!$name) {
            $error = 'Item name is required.';
        } elseif ($price === false || $price < 0) {
            $error = 'Please enter a valid price.';
        } else {
            $stmt = $pdo->prepare("UPDATE menu_items SET name=?, description=?, price=?, category=?, available=?, image=? WHERE id=?");
            $stmt->execute([$name, $desc, $price, $cat, $avail, $img, $id]);
            $msg = "Item updated!";
        }
    }

    if (isset($_POST['delete_item'])) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id) {
            // Optionally delete image file
            $stmt = $pdo->prepare("SELECT image FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);
            $img = $stmt->fetchColumn();
            if ($img && file_exists(__DIR__ . '/images/' . $img)) {
                @unlink(__DIR__ . '/images/' . $img);
            }
            $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);
            $msg = 'Item deleted.';
        }
    }
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
        <?php elseif ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- ===== ADD NEW ITEM FORM ===== -->
        <section class="form-section">
            <h2>Add New Item</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <input type="text"   name="name"        placeholder="Item Name"                    required>
                <textarea            name="description"  placeholder="Description"></textarea>
                <input type="number" name="price"        placeholder="Price" step="0.01" min="0"   required>
                <input type="text"   name="category"     placeholder="Category (e.g. Pizza, Pasta)">
                <input type="file"   name="image" accept="image/*">
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
                    <form method="POST" enctype="multipart/form-data" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                        <input type="hidden" name="id"          value="<?= $item['id'] ?>">
                        <input type="hidden" name="old_image"   value="<?= htmlspecialchars($item['image'] ?? '') ?>">
                        <input type="text"   name="name"        value="<?= htmlspecialchars($item['name']) ?>"        required>
                        <input type="number" name="price"       value="<?= $item['price'] ?>"                         step="0.01">
                        <input type="text"   name="category"    value="<?= htmlspecialchars($item['category']) ?>">
                        <input type="text"   name="description" value="<?= htmlspecialchars($item['description']) ?>">
                        <input type="file"   name="image" accept="image/*">
                        <?php if (!empty($item['image'])): ?>
                            <br><img src="images/<?= htmlspecialchars($item['image']) ?>" alt="Current Image" style="max-width:60px; max-height:60px; margin:0.5em 0;">
                        <?php endif; ?>
                        <label>
                            <input type="checkbox" name="available" <?= $item['available'] ? 'checked' : '' ?>>
                            Available
                        </label>
                        <button type="submit" name="update_item" class="btn">Update</button>
                    </form>

                    <form method="POST" style="display:inline; margin-left:0.5rem;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" name="delete_item" class="btn btn-danger" onclick="return confirm('Delete this item?')">Delete</button>
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