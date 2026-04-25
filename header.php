<?php
// Always include auth.php so isLoggedIn() and isAdmin() are available
require_once __DIR__ . '/auth.php';
?>

<nav class="navbar">
    <div class="logo">ZimBites Restaurant</div>
    <ul class="nav-links">
        <li><a href="logout_home.php">Home</a></li>
        <li><a href="about.html">About Us</a></li>
        <?php if (isLoggedIn()): ?>
            <?php if (isAdmin()): ?>
                <li><a href="admin_dashboard.php">Admin Panel</a></li>
                <li><a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>)</a></li>
            <?php else: ?>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="dashboard.php">My Orders</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>)</a></li>
            <?php endif; ?>
        <?php else: ?>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>