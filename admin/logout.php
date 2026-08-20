<?php
/**
 * Logout script — Somali Cardiac Society
 */
require_once __DIR__ . '/../config/auth.php';

// Clear session
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect to hidden portal login
header('Location: ' . SITE_URL . '/admin/scs-portal-login.php');
exit;
