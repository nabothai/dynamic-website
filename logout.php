<?php
// Start the session so we can access and destroy it
session_start();

// Remove all session data (logs the user out)
session_destroy();

// Send the user back to the login page after logging out
header("Location: login.php");
exit();
?>