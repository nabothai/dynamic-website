<?php
// Start a session only if one isn't already running
// Sessions let us remember who is logged in across pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
// Returns true if the session has a user_id stored in it
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if the logged-in user has the 'admin' role
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Protect any page that requires login
// If user is not logged in, redirect them to the login page
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit(); // Stop the rest of the page from loading
    }
}

// Protect any page that requires admin access
// If user is not an admin, send them back to the home page
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}
?>