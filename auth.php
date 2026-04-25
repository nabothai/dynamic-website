<?php
// Start a session only if one isn't already running
// Sessions let us remember who is logged in across pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if the logged-in user has the 'admin' role
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Protect any page that requires login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Protect any page that requires admin access
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}

// Generate or return the current CSRF token for the session
function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify a submitted CSRF token
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>