<?php
require_once __DIR__ . '/config.php';

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_BASE . '/login.php');
        exit();
    }
}

function attemptAdminLogin($username, $password) {
    global $adminConfig;
    
    if ($username === $adminConfig['username'] && $password === $adminConfig['password']) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $adminConfig['username'];
        $_SESSION['admin_last_activity'] = time();
        return true;
    }
    
    return false;
}
?>
