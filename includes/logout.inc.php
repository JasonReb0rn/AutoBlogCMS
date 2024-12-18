<?php
session_start();
session_unset();     // Clear all session variables
session_destroy();   // Destroy the session
session_write_close(); // Make sure the session is written and closed

// Clear the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Redirect to login page
header("Location: ../login.php");
exit();