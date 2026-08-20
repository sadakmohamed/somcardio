<?php
/**
 * Administrator Profile Settings — Somali Cardiac Society
 */
require_once __DIR__ . '/../includes/admin_layout.php';

$db = getDB();
$adminId = (int)$_SESSION['admin_id'];

$error = null;
$success = null;

// Fetch current user details
try {
    $stmt = $db->prepare("SELECT * FROM admins WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $adminId]);
    $adminUser = $stmt->fetch();
    if (!$adminUser) {
        setFlash('error', 'Account not found.');
        header('Location: logout.php');
        exit;
    }
} catch (Exception $ex) {
    setFlash('error', 'Failed to retrieve profile.');
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid security token.';
    } else {
        $formType = $_POST['form_type'] ?? '';

        if ($formType === 'profile') {
            // Update profile info
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');

            if (empty($fullName) || empty($email) || empty($username)) {
                $error = 'All profile fields are required.';
            } else {
                // Check unique email/username
                $dupCheck = $db->prepare("SELECT COUNT(*) FROM admins WHERE (username = :username OR email = :email) AND id != :id");
                $dupCheck->execute([':username' => $username, ':email' => $email, ':id' => $adminId]);
                if ($dupCheck->fetchColumn() > 0) {
                    $error = 'Username or Email is already in use by another account.';
                } else {
                    try {
                        $update = $db->prepare("UPDATE admins SET full_name = :full_name, email = :email, username = :username WHERE id = :id");
                        $update->execute([
                            ':full_name' => $fullName,
                            ':email' => $email,
                            ':username' => $username,
                            ':id' => $adminId
                        ]);
                        
                        // Update session variables
                        $_SESSION['admin_name'] = $fullName;
                        $_SESSION['admin_user'] = $username;
                        
                        $success = 'Profile details updated successfully.';
                        
                        // Refresh page details
                        $adminUser['full_name'] = $fullName;
                        $adminUser['email'] = $email;
                        $adminUser['username'] = $username;
                    } catch (Exception $ex) {
                        $error = 'Database update failed: ' . $ex->getMessage();
                    }
                }
            }
        } elseif ($formType === 'password') {
            // Update password
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $error = 'All password fields are required.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match.';
            } else {
                // Verify current password
                if (!password_verify($currentPassword, $adminUser['password'])) {
                    $error = 'Incorrect current password.';
                } else {
                    try {
                        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                        $updatePass = $db->prepare("UPDATE admins SET password = :password WHERE id = :id");
                        $updatePass->execute([
                            ':password' => $newHash,
                            ':id' => $adminId
                        ]);
                        $success = 'Password changed successfully.';
                    } catch (Exception $ex) {
                        $error = 'Failed to change password: ' . $ex->getMessage();
                    }
                }
            }
        }
    }
}

startAdminLayout('My Settings');
?>

<div class="page-title-block">
    <div>
        <h1>My Settings</h1>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Manage your login credentials and personal profile.</p>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom: 24px;"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom: 24px;"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="dash-grid profile-grid">
    
    <!-- Profile Info Form -->
    <div class="admin-card">
        <div class="card-header">
            <h2>Personal Profile</h2>
        </div>
        <div class="card-body">
            <form action="profile.php" method="POST" class="admin-form">
                <?php echo csrfField(); ?>
                <input type="hidden" name="form_type" value="profile">

                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($adminUser['full_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($adminUser['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($adminUser['username']); ?>">
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn-admin btn-admin-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Change Form -->
    <div class="admin-card">
        <div class="card-header">
            <h2>Security &amp; Password</h2>
        </div>
        <div class="card-body">
            <form action="profile.php" method="POST" class="admin-form">
                <?php echo csrfField(); ?>
                <input type="hidden" name="form_type" value="password">

                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required placeholder="Enter current password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required placeholder="Enter new password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Retype new password">
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn-admin btn-admin-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php endAdminLayout(); ?>
