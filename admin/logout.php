<?php
require_once __DIR__ . '/../includes/auth.php';

// Destroy session
$_SESSION = [];
session_destroy();

// Redirect to login
header('Location: login.php');
exit();
?>