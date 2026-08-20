<?php
/**
 * Admin Dashboard — Somali Cardiac Society
 */
require_once __DIR__ . '/../includes/admin_layout.php';

try {
    $db = getDB();

    $totalMembers = $db->query("SELECT COUNT(*) FROM members")->fetchColumn();
    $totalContent = $db->query("SELECT COUNT(*) FROM content")->fetchColumn();
    $totalAdmins  = $db->query("SELECT COUNT(*) FROM admins")->fetchColumn();

    $recentContent = $db->query("SELECT * FROM content ORDER BY created_at DESC LIMIT 6")->fetchAll();
} catch (Exception $ex) {
    $totalMembers  = 0;
    $totalContent  = 0;
    $totalAdmins   = 0;
    $recentContent = [];
}

startAdminLayout('Dashboard');
?>

<div class="page-title-block">
    <div>
        <h1>Welcome back, <?php echo htmlspecialchars($adminFullName); ?> 👋</h1>
        <p>Here's what's happening with the Somali Cardiac Society platform today.</p>
    </div>
    <div class="page-title-actions">
        <a href="content.php" class="btn-admin btn-admin-primary">
            <i class="ph ph-plus" style="font-size:1rem;"></i> New Content
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="dashboard-stats">

    <!-- Members -->
    <div class="stat-box">
        <div class="stat-box-left">
            <h3>Total Members</h3>
            <div class="number"><?php echo $totalMembers; ?></div>
        </div>
        <div class="stat-box-icon" style="background:rgba(39,170,225,0.1);color:#27AAE1;">
            <i class="ph ph-users-three"></i>
        </div>
    </div>

    <!-- Content -->
    <div class="stat-box">
        <div class="stat-box-left">
            <h3>Published Content</h3>
            <div class="number"><?php echo $totalContent; ?></div>
        </div>
        <div class="stat-box-icon" style="background:rgba(16,185,129,0.1);color:#10b981;">
            <i class="ph ph-article"></i>
        </div>
    </div>

    <!-- Admin count — Super Admin only -->
    <?php if (isSuperAdmin()): ?>
    <div class="stat-box">
        <div class="stat-box-left">
            <h3>Admin Users</h3>
            <div class="number"><?php echo $totalAdmins; ?></div>
        </div>
        <div class="stat-box-icon" style="background:rgba(139,92,246,0.1);color:#8b5cf6;">
            <i class="ph ph-shield-check"></i>
        </div>
    </div>
    <?php else: ?>
    <!-- Placeholder for regular admin -->
    <div class="stat-box">
        <div class="stat-box-left">
            <h3>Upcoming Events</h3>
            <div class="number">
                <?php
                try {
                    echo $db->query("SELECT COUNT(*) FROM content WHERE category='events' AND is_published=1 AND event_date >= CURDATE()")->fetchColumn();
                } catch (Exception $e) { echo 0; }
                ?>
            </div>
        </div>
        <div class="stat-box-icon" style="background:rgba(237,28,36,0.1);color:#ED1C24;">
            <i class="ph ph-calendar-check"></i>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Recent Content Table -->
<div class="dash-grid single-col">
    <div class="admin-card">
        <div class="card-header">
            <h2>Recent Content Updates</h2>
            <a href="content.php" class="btn-admin btn-admin-secondary btn-sm">View All</a>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-responsive">
                <table class="admin-table" id="dataTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentContent)): ?>
                            <?php foreach ($recentContent as $item): ?>
                            <tr>
                                <td>
                                    <strong style="display:block;font-weight:600;color:#0D1B2A;"><?php echo htmlspecialchars($item['title']); ?></strong>
                                    <span style="font-size:0.78rem;color:var(--text-light);"><?php echo htmlspecialchars($item['author']); ?></span>
                                </td>
                                <td>
                                    <span class="status-badge badge-blue"><?php echo ucfirst($item['category']); ?></span>
                                </td>
                                <td style="color:var(--text-secondary);font-size:0.875rem;">
                                    <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $item['is_published'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $item['is_published'] ? 'Published' : 'Draft'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center;color:var(--text-light);padding:56px;">
                                    <i class="ph ph-folder-open" style="font-size:2.5rem;margin-bottom:10px;display:block;opacity:0.4;"></i>
                                    No content items yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php endAdminLayout(); ?>
