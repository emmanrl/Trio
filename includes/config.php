<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
session_start([
    'cookie_lifetime' => 86400, // 1 day
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);

// Constants
define('BASE_URL', 'http://192.168.1.116:8158');
define('ADMIN_BASE', BASE_URL . '/admin');
// Admin credentials (for direct password comparison)

$adminConfig = [
    'username' => 'admin',
    'password' => 'admin123' // Change this to your desired admin password
];


// Database connection
require_once __DIR__ . '/db.php';
?>