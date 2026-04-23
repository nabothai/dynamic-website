<?php
// Always include auth.php so isLoggedIn() and isAdmin() are available
require_once __DIR__ . '/auth.php';
?>

<nav class="navbar">
    <!-- Restaurant logo / home link -->
    <a href="index.php" class="logo">🍕 Bella Italia</a>

    <ul>
        <!-- These links appear for ALL visitors -->
        <li><a href="index.php">Home</a></li>
        <li><a href="menu.php">Menu</a></li>

        <?php if (isLoggedIn()): ?>
            <!-- Extra links shown only to logged-in users -->
            <li><a href="dashboard.php">My Orders</a></li>

            <?php if (isAdmin()): ?>
                <!-- Admin-only link to the admin dashboard -->
                <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
            <?php endif; ?>

            <!-- Show the logged-in user's name and a logout link -->
            <li><a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['name']) ?>)</a></li>

        <?php else: ?>
            <!-- Show Login and Register links for guests -->
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>