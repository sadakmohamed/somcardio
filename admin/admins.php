<?php
/**
 * Administrator Users Management — Somali Cardiac Society (Super Admin Only)
 */
require_once __DIR__ . '/../includes/admin_layout.php';

// Strict Role check
requireSuperAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$error = null;
$success = null;

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        setFlash('error', 'Invalid security token.');
        header('Location: admins.php');
        exit;
    }

    if ($action === 'add' || $action === 'edit') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $role = trim($_POST['role'] ?? 'admin');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($username) || empty($email) || empty($fullName)) {
            $error = 'Username, Email and Full Name are required.';
        } elseif ($action === 'add' && empty($password)) {
            $error = 'Password is required for new accounts.';
        } elseif (!empty($password) && $password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            // Verify unique username/email
            $userCheck = $db->prepare("SELECT COUNT(*) FROM admins WHERE (username = :username OR email = :email) AND id != :id");
            $userCheck->execute([':username' => $username, ':email' => $email, ':id' => $id]);
            if ($userCheck->fetchColumn() > 0) {
                $error = 'Username or Email is already in use.';
            } else {
                try {
                    if ($action === 'add') {
                        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $db->prepare("INSERT INTO admins (username, email, password, full_name, role, is_active) VALUES (:username, :email, :password, :full_name, :role, :is_active)");
                        $stmt->execute([
                            ':username' => $username,
                            ':email' => $email,
                            ':password' => $hashedPassword,
                            ':full_name' => $fullName,
                            ':role' => $role,
                            ':is_active' => $isActive
                        ]);
                        setFlash('success', 'Admin user created successfully.');
                    } else {
                        // Edit existing
                        if (!empty($password)) {
                            // Update details with password
                            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                            $stmt = $db->prepare("UPDATE admins SET username = :username, email = :email, password = :password, full_name = :full_name, role = :role, is_active = :is_active WHERE id = :id");
                            $stmt->execute([
                                ':username' => $username,
                                ':email' => $email,
                                ':password' => $hashedPassword,
                                ':full_name' => $fullName,
                                ':role' => $role,
                                ':is_active' => $isActive,
                                ':id' => $id
                            ]);
                        } else {
                            // Update details without password
                            $stmt = $db->prepare("UPDATE admins SET username = :username, email = :email, full_name = :full_name, role = :role, is_active = :is_active WHERE id = :id");
                            $stmt->execute([
                                ':username' => $username,
                                ':email' => $email,
                                ':full_name' => $fullName,
                                ':role' => $role,
                                ':is_active' => $isActive,
                                ':id' => $id
                            ]);
                        }
                        
                        // Prevent locking oneself out of Super Admin role if editing own account
                        if ($id === (int)$_SESSION['admin_id']) {
                            $_SESSION['admin_name'] = $fullName;
                            $_SESSION['admin_role'] = $role;
                            $_SESSION['admin_user'] = $username;
                        }

                        setFlash('success', 'Admin user updated successfully.');
                    }
                    header('Location: admins.php');
                    exit;
                } catch (Exception $ex) {
                    $error = 'Database error: ' . $ex->getMessage();
                }
            }
        }
    }
}

// Handle Delete
if ($action === 'delete' && $id > 0) {
    if ($id === (int)$_SESSION['admin_id']) {
        setFlash('error', 'You cannot delete your own admin account.');
    } else {
        try {
            $stmt = $db->prepare("DELETE FROM admins WHERE id = :id");
            $stmt->execute([':id' => $id]);
            setFlash('success', 'Admin user deleted successfully.');
        } catch (Exception $ex) {
            setFlash('error', 'Failed to delete admin user.');
        }
    }
    header('Location: admins.php');
    exit;
}

// Display Views
if ($action === 'add' || $action === 'edit') {
    $adminUser = ['username' => '', 'email' => '', 'full_name' => '', 'role' => 'admin', 'is_active' => 1];
    if ($action === 'edit' && $id > 0) {
        $stmt = $db->prepare("SELECT * FROM admins WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $adminUser = $stmt->fetch();
        if (!$adminUser) {
            setFlash('error', 'Admin user not found.');
            header('Location: admins.php');
            exit;
        }
    }

    startAdminLayout(($action === 'add' ? 'Create' : 'Edit') . ' Admin User');
    ?>
    <div class="page-title-block">
        <div>
            <h1><?php echo $action === 'add' ? 'Create Admin User' : 'Edit Admin User Details'; ?></h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Assign access levels and configure login credentials.</p>
        </div>
        <a href="admins.php" class="btn-admin btn-admin-secondary">Back to List</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="admin-card" style="max-width: 600px;">
        <div class="card-body">
            <form action="admins.php?action=<?php echo $action; ?>&id=<?php echo $id; ?>" method="POST" class="admin-form">
                <?php echo csrfField(); ?>

                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($adminUser['username']); ?>" placeholder="Enter unique username">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($adminUser['email']); ?>" placeholder="Enter unique email address">
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($adminUser['full_name']); ?>" placeholder="e.g. Dr. Ahmed Hassan">
                    </div>

                    <div class="form-group">
                        <label for="role">Role Permission *</label>
                        <select id="role" name="role" required>
                            <option value="admin" <?php echo $adminUser['role'] === 'admin' ? 'selected' : ''; ?>>Admin (Content manager)</option>
                            <option value="super_admin" <?php echo $adminUser['role'] === 'super_admin' ? 'selected' : ''; ?>>Super Admin (Master access)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="password">Password <?php echo $action === 'edit' ? '(Leave blank to keep current)' : '*'; ?></label>
                        <input type="password" id="password" name="password" <?php echo $action === 'add' ? 'required' : ''; ?> placeholder="Enter password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" <?php echo $action === 'add' ? 'required' : ''; ?> placeholder="Retype password">
                    </div>

                    <div class="form-group" style="padding-top: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                            <input type="checkbox" name="is_active" value="1" <?php echo $adminUser['is_active'] ? 'checked' : ''; ?>>
                            Account Active (Allows login)
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="admins.php" class="btn-admin btn-admin-secondary">Cancel</a>
                    <button type="submit" class="btn-admin btn-admin-primary">Save Account</button>
                </div>
            </form>
        </div>
    </div>
    <?php
    endAdminLayout();
} else {
    // List View
    try {
        $stmt = $db->query("SELECT * FROM admins ORDER BY created_at DESC");
        $adminsList = $stmt->fetchAll();
    } catch (Exception $ex) {
        $adminsList = [];
    }

    startAdminLayout('Manage Admin Users');
    ?>
    <div class="page-title-block">
        <div>
            <h1>Manage Admin Accounts</h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Exclusive Super Admin control over administrator access keys.</p>
        </div>
        <a href="admins.php?action=add" class="btn-admin btn-admin-primary">Create Admin User</a>
    </div>

    <!-- Search Card -->
    <div class="admin-card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 15px 24px;">
            <input type="text" id="tableSearch" placeholder="Search admin accounts..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 0.9rem;">
        </div>
    </div>

    <div class="admin-card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="admin-table" id="dataTable">
                    <thead>
                        <tr>
                            <th>User Profile</th>
                            <th>Username</th>
                            <th>Role Level</th>
                            <th>Last Login</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($adminsList)): ?>
                            <?php foreach ($adminsList as $usr): ?>
                            <tr>
                                <td>
                                    <div class="user-info-cell">
                                        <div class="user-avatar-small" style="<?php echo $usr['role'] === 'super_admin' ? 'background: #fef2f2; color: var(--primary-red);' : ''; ?>">
                                            <?php echo strtoupper(substr($usr['full_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($usr['full_name']); ?></strong><br>
                                            <span style="font-size: 0.75rem; color: var(--text-secondary);"><?php echo htmlspecialchars($usr['email']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($usr['username']); ?></td>
                                <td>
                                    <span class="user-role role-<?php echo $usr['role'] === 'super_admin' ? 'super' : 'admin'; ?>">
                                        <?php echo $usr['role'] === 'super_admin' ? 'Super Admin' : 'Admin'; ?>
                                    </span>
                                </td>
                                <td><?php echo $usr['last_login'] ? date('M d, Y @ h:i A', strtotime($usr['last_login'])) : '<span style="color:var(--text-light); font-size: 0.8rem;">Never</span>'; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $usr['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $usr['is_active'] ? 'Active' : 'Deactivated'; ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="admins.php?action=edit&id=<?php echo $usr['id']; ?>" class="btn-icon" title="Edit Admin Settings">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    <?php if ($usr['id'] !== (int)$_SESSION['admin_id']): ?>
                                        <a href="admins.php?action=delete&id=<?php echo $usr['id']; ?>" class="btn-icon btn-icon-danger confirm-delete" data-item="admin user" title="Delete Account">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </a>
                                    <?php else: ?>
                                        <span class="btn-icon" style="opacity: 0.3; cursor: not-allowed;" title="Cannot delete yourself">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-light); padding: 50px;">No admins registered.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    endAdminLayout();
}
?>
