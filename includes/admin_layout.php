<?php
/**
 * Admin Panel Layout Wrapper — Somali Cardiac Society
 */
require_once __DIR__ . '/../config/auth.php';

// Force admin login
requireLogin();

$currentAdminPage = basename($_SERVER['PHP_SELF']);
$adminFullName    = $_SESSION['admin_name'] ?? 'Administrator';
$adminRole        = $_SESSION['admin_role'] ?? 'admin';
$adminUsername    = $_SESSION['admin_user'] ?? 'admin';

/**
 * Render the full admin shell header + sidebar
 */
function startAdminLayout(string $title) {
    global $currentAdminPage, $adminFullName, $adminRole, $adminUsername;
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> — SCS Admin Panel</title>
        <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/images/logo-2.png">
        <script src="https://unpkg.com/@phosphor-icons/web"></script>
        <!-- SweetAlert2 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    </head>
    <body>
    <div class="admin-wrapper">

        <!-- ── Sidebar ── -->
        <aside class="admin-sidebar" id="adminSidebar">

            <!-- Brand -->
            <div class="sidebar-brand">
                <img src="<?php echo SITE_URL; ?>/images/logo.png" alt="SCS Logo">
                <span>SCS Portal</span>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">

                <div class="sidebar-label">Main Menu</div>

                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php"
                   class="<?php echo $currentAdminPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="ph ph-squares-four"></i>
                    Dashboard
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/members.php"
                   class="<?php echo $currentAdminPage === 'members.php' ? 'active' : ''; ?>">
                    <i class="ph ph-users"></i>
                    Manage Members
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/content.php"
                   class="<?php echo $currentAdminPage === 'content.php' ? 'active' : ''; ?>">
                    <i class="ph ph-file-text"></i>
                    Manage Content
                </a>

                <?php if (isSuperAdmin()): ?>
                <div class="sidebar-label">Admin</div>
                <a href="<?php echo SITE_URL; ?>/admin/admins.php"
                   class="<?php echo $currentAdminPage === 'admins.php' ? 'active' : ''; ?>">
                    <i class="ph ph-shield-check"></i>
                    Admin Users
                </a>
                <?php endif; ?>

                <div class="sidebar-label">Account</div>

                <a href="<?php echo SITE_URL; ?>/admin/profile.php"
                   class="<?php echo $currentAdminPage === 'profile.php' ? 'active' : ''; ?>">
                    <i class="ph ph-gear"></i>
                    Settings
                </a>

                <a href="<?php echo SITE_URL; ?>/" target="_blank">
                    <i class="ph ph-arrow-square-out"></i>
                    View Website
                </a>

            </nav>

            <!-- Footer: user info + logout -->
            <div class="sidebar-footer">
                <div class="sf-avatar">
                    <?php echo strtoupper(substr($adminFullName, 0, 1)); ?>
                </div>
                <div class="sf-info">
                    <div class="sf-name"><?php echo htmlspecialchars($adminFullName); ?></div>
                    <div class="sf-role"><?php echo $adminRole === 'super_admin' ? 'Super Admin' : 'Admin'; ?></div>
                </div>
                <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="sf-logout" title="Logout">
                    <i class="ph ph-sign-out" style="font-size:1.2rem;"></i>
                </a>
            </div>
        </aside>

        <!-- ── Main Content ── -->
        <main class="admin-main">

            <!-- Top Header -->
            <header class="admin-header">
                <button class="header-toggle" id="headerToggle" aria-label="Toggle Sidebar">
                    <i class="ph ph-list" style="font-size:1.5rem;"></i>
                </button>

                <div></div><!-- Spacer -->

                <div class="header-user">
                    <div>
                        <div class="user-name"><?php echo htmlspecialchars($adminFullName); ?></div>
                        <span class="user-role role-<?php echo $adminRole === 'super_admin' ? 'super' : 'admin'; ?>">
                            <?php echo $adminRole === 'super_admin' ? 'Super Admin' : 'Admin'; ?>
                        </span>
                    </div>
                    <div class="user-avatar-small">
                        <?php echo strtoupper(substr($adminFullName, 0, 1)); ?>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="admin-content">

                <!-- Flash messages -->
                <?php
                $successFlash = getFlash('success');
                $errorFlash   = getFlash('error');
                if ($successFlash): ?>
                    <div class="alert alert-success" style="margin-bottom:24px;"><?php echo $successFlash; ?></div>
                <?php endif;
                if ($errorFlash): ?>
                    <div class="alert alert-error" style="margin-bottom:24px;"><?php echo $errorFlash; ?></div>
                <?php endif; ?>
    <?php
}

/**
 * Close the admin layout shell
 */
function endAdminLayout() {
    ?>
            </div><!-- /admin-content -->
        </main><!-- /admin-main -->
    </div><!-- /admin-wrapper -->

    <script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>

    <!-- Confirm-delete via SweetAlert -->
    <script>
    document.querySelectorAll('.confirm-delete').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.href;
            const item = this.dataset.item || 'item';
            Swal.fire({
                title: 'Delete ' + item + '?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ED1C24',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                borderRadius: '16px'
            }).then(function(result) {
                if (result.isConfirmed) window.location.href = href;
            });
        });
    });

    // Table search
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#dataTable tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }
    </script>
    </body>
    </html>
    <?php
    echo ob_get_clean();
}
