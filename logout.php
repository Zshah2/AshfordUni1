<?php
session_start();

// Remove all session information
$_SESSION = [];

// End the session
session_destroy();

// Return to the login page
header('Location: index.php');
exit;
?>