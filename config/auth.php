<?php
/**
 * Authentication & Session Management — Somali Cardiac Society
 * Handles session security, login checks, RBAC, and CSRF protection
 */

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false, // Set to true in production with HTTPS
        'httponly'  => true,
        'samesite'  => 'Strict'
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require login — redirect to login page if not authenticated
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/scs-portal-login.php');
        exit;
    }
}

/**
 * Check if current user is Super Admin
 */
function isSuperAdmin(): bool {
    return isLoggedIn() && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

/**
 * Require Super Admin role
 */
function requireSuperAdmin(): void {
    requireLogin();
    if (!isSuperAdmin()) {
        $_SESSION['flash_error'] = 'Access denied. Super Admin privileges required.';
        header('Location: ' . SITE_URL . '/admin/dashboard.php');
        exit;
    }
}

/**
 * Authenticate admin user
 */
function authenticateAdmin(string $username, string $password): array|false {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admins WHERE username = :username AND is_active = 1 LIMIT 1");
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        // Update last login
        $update = $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = :id");
        $update->execute([':id' => $admin['id']]);
        
        // Set session
        session_regenerate_id(true);
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_user'] = $admin['username'];
        
        return $admin;
    }
    
    return false;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Render CSRF hidden input
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

/**
 * Sanitize output string
 */
function e(string|null $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Get and clear flash message
 */
function getFlash(string $type): string|null {
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return null;
}

/**
 * Generate URL-safe slug from title
 */
function generateSlug(string $title): string {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Handle file upload (images)
 */
function handleImageUpload(array $file, string $subfolder): string|false {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if (!in_array($file['type'], $allowed)) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('scs_', true) . '.' . strtolower($ext);
    $uploadDir = UPLOADS_DIR . '/' . $subfolder;
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $destination = $uploadDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $subfolder . '/' . $filename;
    }
    
    return false;
}

/**
 * Delete uploaded file
 */
function deleteUploadedFile(string|null $filepath): bool {
    if (!$filepath) return false;
    $fullPath = UPLOADS_DIR . '/' . $filepath;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}
