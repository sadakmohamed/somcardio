<?php
/**
 * Admin Login Page — Somali Cardiac Society
 */
require_once __DIR__ . '/../config/auth.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            $admin = authenticateAdmin($username, $password);
            if ($admin) {
                header('Location: ' . SITE_URL . '/admin/dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password, or account deactivated.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCS Admin Portal — Login</title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/images/logo-2.png">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
</head>
<body class="login-body">

<div class="login-box">
    <div class="login-logo">
        <img src="<?php echo SITE_URL; ?>/images/logo-2.png" alt="SCS Logo">
        <h1>SCS Admin Portal</h1>
        <p>Secure access for authorized personnel only</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom:24px;border-radius:10px;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="scs-portal-login.php" method="POST">
        <?php echo csrfField(); ?>

        <div style="margin-bottom:18px;">
            <label for="username" style="color:rgba(255,255,255,0.7);text-transform:uppercase;font-size:0.72rem;letter-spacing:0.07em;font-weight:700;margin-bottom:8px;display:block;">Username</label>
            <input type="text" id="username" name="username" required
                   placeholder="Enter your username"
                   autocomplete="username">
        </div>

        <div style="margin-bottom:28px;">
            <label for="password" style="color:rgba(255,255,255,0.7);text-transform:uppercase;font-size:0.72rem;letter-spacing:0.07em;font-weight:700;margin-bottom:8px;display:block;">Password</label>
            <input type="password" id="password" name="password" required
                   placeholder="Enter your password"
                   autocomplete="current-password">
        </div>

        <button type="submit" class="btn-admin btn-admin-primary" style="width:100%;padding:14px;font-size:0.95rem;border-radius:10px;justify-content:center;">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/></svg>
            Sign In to Portal
        </button>
    </form>

    <div style="text-align:center;margin-top:28px;">
        <a href="<?php echo SITE_URL; ?>/" style="font-size:0.82rem;color:rgba(255,255,255,0.35);transition:color 0.2s;"
           onmouseover="this.style.color='#27AAE1'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">
            ← Back to Main Website
        </a>
    </div>
</div>

</body>
</html>
