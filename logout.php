<?php
// Start session
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login.php
header("Location: login.php");
exit();
?>
