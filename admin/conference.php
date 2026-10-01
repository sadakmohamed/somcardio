<?php
/**
 * Conference Hub — Admin Overview
 * Somali Cardiac Society
 *
 * Displays key stats (speakers, registrations, abstract deadline, past conferences)
 * and provides quick-links to all conference management sections.
 */
require_once __DIR__ . '/../includes/admin_layout.php';

$db = getDB();

/* ── Stats ─────────────────────────────────────────────────── */
try {
    // Fetch active (ongoing) conference
    $stmtConf = $db->prepare(
        "SELECT * FROM conferences WHERE status = 'active' ORDER BY year DESC, id DESC LIMIT 1"
    );
    $stmtConf->execute();
    $activeConf = $stmtConf->fetch();
    $confId = $activeConf ? (int)$activeConf['id'] : 0;

    // Speakers count (active)
    $totalSpeakers = 0;
    if ($confId) {
        $stmtSp = $db->prepare(
            "SELECT COUNT(*) FROM conference_speakers WHERE conference_id = :cid AND is_active = 1"
        );
        $stmtSp->execute([':cid' => $confId]);
        $totalSpeakers = (int)$stmtSp->fetchColumn();
    }

    // Registration counts
    $regPending  = 0; $regApproved = 0; $regDeclined = 0; $regTotal = 0;
    if ($confId) {
        $stmtReg = $db->prepare(
            "SELECT status, COUNT(*) AS cnt
             FROM conference_registrations
             WHERE conference_id = :cid
             GROUP BY status"
        );
        $stmtReg->execute([':cid' => $confId]);
        foreach ($stmtReg->fetchAll() as $row) {
            $regTotal += (int)$row['cnt'];
            if ($row['status'] === 'pending')  $regPending  = (int)$row['cnt'];
            if ($row['status'] === 'approved') $regApproved = (int)$row['cnt'];
            if ($row['status'] === 'declined') $regDeclined = (int)$row['cnt'];
        }
    }

    // Abstract deadline
    $deadline = null;
    if ($confId) {
        $stmtDeadline = $db->prepare(
            "SELECT submission_deadline FROM conference_abstract_settings WHERE conference_id = :cid LIMIT 1"
        );
        $stmtDeadline->execute([':cid' => $confId]);
        $deadline = $stmtDeadline->fetchColumn() ?: null;
    }
    $deadlineTs  = $deadline ? strtotime($deadline) : null;
    $daysLeft    = $deadlineTs ? (int)ceil(($deadlineTs - time()) / 86400) : null;

    // Past conferences count
    $stmtPast = $db->query("SELECT COUNT(*) FROM past_conferences");
    $totalPast = (int)$stmtPast->fetchColumn();

    // Recent 5 registrations
    $recentRegs = [];
    if ($confId) {
        $stmtRecent = $db->prepare(
            "SELECT *, CONCAT(first_name, ' ', last_name) AS full_name FROM conference_registrations
             WHERE conference_id = :cid
             ORDER BY registered_at DESC LIMIT 5"
        );
        $stmtRecent->execute([':cid' => $confId]);
        $recentRegs = $stmtRecent->fetchAll();
    }

} catch (Exception $ex) {
    $activeConf  = null; $confId = 0;
    $totalSpeakers = $regTotal = $regPending = $regApproved = $regDeclined = 0;
    $deadline = $deadlineTs = $daysLeft = null;
    $totalPast = 0; $recentRegs = [];
}

startAdminLayout('Conference Hub');
?>

<!-- ── Page Title ─────────────────────────────────────────── -->
<div class="page-title-block">
    <div>
        <h1><i class="ph ph-presentation" style="color:#27AAE1;margin-right:10px;"></i>Conference Hub</h1>
        <p>Overview of the <?php echo $activeConf ? e($activeConf['title']) : 'active conference'; ?> and all related modules.</p>
    </div>
    <div class="page-title-actions">
        <a href="<?php echo SITE_URL; ?>/admin/conference-ongoing" class="btn-admin btn-admin-primary">
            <i class="ph ph-pencil-simple" style="font-size:1rem;"></i> Edit Conference
        </a>
    </div>
</div>

<?php if ($activeConf): ?>
<!-- ── Active Conference Banner ────────────────────────────── -->
<div class="admin-card" style="margin-bottom:28px;background:linear-gradient(135deg,#0D1B2A 0%,#1a3450 100%);color:#fff;border:none;">
    <div class="card-body" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
        <?php if ($activeConf['hero_image']): ?>
            <img src="<?php echo UPLOADS_URL . '/' . e($activeConf['hero_image']); ?>"
                 alt="Hero" style="width:90px;height:60px;object-fit:cover;border-radius:10px;flex-shrink:0;">
        <?php else: ?>
            <div style="width:90px;height:60px;border-radius:10px;background:rgba(39,170,225,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="ph ph-presentation" style="font-size:2rem;color:#27AAE1;"></i>
            </div>
        <?php endif; ?>
        <div style="flex:1;">
            <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.08em;color:#27AAE1;font-weight:600;margin-bottom:4px;">
                Active Conference
            </div>
            <h2 style="margin:0;font-size:1.25rem;font-weight:700;color:#fff;">
                <?php echo e($activeConf['title']); ?>
            </h2>
            <span style="font-size:0.85rem;opacity:0.6;"><?php echo e($activeConf['year']); ?></span>
        </div>
        <span style="padding:6px 16px;border-radius:999px;background:rgba(39,170,225,0.2);color:#27AAE1;font-size:0.82rem;font-weight:600;border:1px solid rgba(39,170,225,0.35);">
            <?php echo $activeConf['status'] === 'active' ? '● Active' : '○ Inactive'; ?>
        </span>
    </div>
</div>
<?php endif; ?>

<!-- ── Stat Cards ───────────────────────────────────────────── -->
<div class="dashboard-stats" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin-bottom:28px;">

    <!-- Speakers -->
    <div class="stat-box">
        <div class="stat-box-left">
            <h3>Active Speakers</h3>
            <div class="number"><?php echo $totalSpeakers; ?></div>
            <a href="<?php echo SITE_URL; ?>/admin/conference-speakers" style="font-size:0.78rem;color:#27AAE1;text-decoration:none;margin-top:4px;display:inline-block;">Manage →</a>
        </div>
        <div class="stat-box-icon" style="background:rgba(39,170,225,0.1);color:#27AAE1;">
            <i class="ph ph-microphone"></i>
        </div>
    </div>

    <!-- Registrations total -->
    <div class="stat-box">
        <div class="stat-box-left">
            <h3>Total Registrations</h3>
            <div class="number"><?php echo $regTotal; ?></div>
            <a href="<?php echo SITE_URL; ?>/admin/conference-registration" style="font-size:0.78rem;color:#27AAE1;text-decoration:none;margin-top:4px;display:inline-block;">Manage →</a>
        </div>
        <div class="stat-box-icon" style="background:rgba(16,185,129,0.1);color:#10b981;">
            <i class="ph ph-users"></i>
        </div>
    </div>

    <!-- Pending -->
    <div class="stat-box">
        <div class="stat-box-left">
            <h3>Pending Review</h3>
            <div class="number" style="color:#f59e0b;"><?php echo $regPending; ?></div>
            <a href="<?php echo SITE_URL; ?>/admin/conference-registration?filter=pending" style="font-size:0.78rem;color:#27AAE1;text-decoration:none;margin-top:4px;display:inline-block;">Review →</a>
        </div>
        <div class="stat-box-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;">
            <i class="ph ph-clock"></i>
        </div>
    </div>

    <!-- Abstract Deadline -->
    <div class="stat-box">
        <div class="stat-box-left">
            <h3>Abstract Deadline</h3>
            <?php if ($daysLeft !== null): ?>
                <div class="number" style="color:<?php echo $daysLeft <= 7 ? '#ED1C24' : ($daysLeft <= 30 ? '#f59e0b' : '#10b981'); ?>;">
                    <?php echo $daysLeft > 0 ? $daysLeft . 'd' : ($daysLeft === 0 ? 'Today' : 'Passed'); ?>
                </div>
                <span style="font-size:0.78rem;color:var(--text-light);">
                    <?php echo date('M d, Y', $deadlineTs); ?>
                </span>
            <?php else: ?>
                <div class="number" style="color:var(--text-light);font-size:1.2rem;">Not set</div>
            <?php endif; ?>
            <a href="<?php echo SITE_URL; ?>/admin/conference-abstracts" style="font-size:0.78rem;color:#27AAE1;text-decoration:none;margin-top:4px;display:inline-block;">Configure →</a>
        </div>
        <div class="stat-box-icon" style="background:rgba(139,92,246,0.1);color:#8b5cf6;">
            <i class="ph ph-file-text"></i>
        </div>
    </div>

    <!-- Past Conferences -->
    <div class="stat-box">
        <div class="stat-box-left">
            <h3>Past Conferences</h3>
            <div class="number"><?php echo $totalPast; ?></div>
            <a href="<?php echo SITE_URL; ?>/admin/conference-past" style="font-size:0.78rem;color:#27AAE1;text-decoration:none;margin-top:4px;display:inline-block;">View Archive →</a>
        </div>
        <div class="stat-box-icon" style="background:rgba(237,28,36,0.1);color:#ED1C24;">
            <i class="ph ph-clock-clockwise"></i>
        </div>
    </div>

</div>

<!-- ── Registration Status Strip ───────────────────────────── -->
<?php if ($regTotal > 0): ?>
<div class="admin-card" style="margin-bottom:28px;">
    <div class="card-header">
        <h2>Registration Status Breakdown</h2>
        <a href="<?php echo SITE_URL; ?>/admin/conference-registration" class="btn-admin btn-admin-secondary btn-sm">View All</a>
    </div>
    <div class="card-body" style="display:flex;gap:24px;flex-wrap:wrap;">
        <?php
        $strips = [
            ['label'=>'Approved','count'=>$regApproved,'color'=>'#10b981','bg'=>'rgba(16,185,129,0.1)'],
            ['label'=>'Pending', 'count'=>$regPending, 'color'=>'#f59e0b','bg'=>'rgba(245,158,11,0.1)'],
            ['label'=>'Declined','count'=>$regDeclined,'color'=>'#ED1C24','bg'=>'rgba(237,28,36,0.1)'],
        ];
        foreach ($strips as $s):
            $pct = $regTotal > 0 ? round(($s['count'] / $regTotal) * 100) : 0;
        ?>
        <div style="flex:1;min-width:160px;padding:20px;border-radius:12px;background:<?php echo $s['bg']; ?>;border:1.5px solid <?php echo $s['color']; ?>22;">
            <div style="font-size:0.78rem;text-transform:uppercase;letter-spacing:.06em;color:<?php echo $s['color']; ?>;font-weight:700;margin-bottom:6px;">
                <?php echo $s['label']; ?>
            </div>
            <div style="font-size:2rem;font-weight:800;color:<?php echo $s['color']; ?>;"><?php echo $s['count']; ?></div>
            <div style="margin-top:8px;background:rgba(0,0,0,0.08);border-radius:999px;height:6px;overflow:hidden;">
                <div style="width:<?php echo $pct; ?>%;height:100%;background:<?php echo $s['color']; ?>;border-radius:999px;"></div>
            </div>
            <div style="font-size:0.78rem;color:var(--text-light);margin-top:4px;"><?php echo $pct; ?>% of total</div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── Quick Links ──────────────────────────────────────────── -->
<div class="admin-card" style="margin-bottom:28px;">
    <div class="card-header"><h2>Quick Actions</h2></div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;">
            <?php
            $quickLinks = [
                ['icon'=>'ph-calendar',     'label'=>'Edit Ongoing Conference', 'url'=>SITE_URL.'/admin/conference-ongoing',    'color'=>'#27AAE1'],
                ['icon'=>'ph-microphone',   'label'=>'Manage Speakers',         'url'=>SITE_URL.'/admin/conference-speakers',   'color'=>'#8b5cf6'],
                ['icon'=>'ph-file-text',    'label'=>'Abstract Settings',       'url'=>SITE_URL.'/admin/conference-abstracts',  'color'=>'#10b981'],
                ['icon'=>'ph-users',        'label'=>'View Registrations',      'url'=>SITE_URL.'/admin/conference-registration','color'=>'#f59e0b'],
                ['icon'=>'ph-clock-clockwise','label'=>'Past Conferences',      'url'=>SITE_URL.'/admin/conference-past',       'color'=>'#ED1C24'],
            ];
            foreach ($quickLinks as $ql):
            ?>
            <a href="<?php echo $ql['url']; ?>" style="display:flex;flex-direction:column;align-items:center;gap:10px;padding:22px 16px;border-radius:14px;background:#F0F4F8;border:1.5px solid #E2E8F0;text-decoration:none;transition:all .2s;color:#0D1B2A;"
               onmouseover="this.style.borderColor='<?php echo $ql['color']; ?>';this.style.background='#fff';this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)';"
               onmouseout="this.style.borderColor='#E2E8F0';this.style.background='#F0F4F8';this.style.boxShadow='';">
                <i class="ph <?php echo $ql['icon']; ?>" style="font-size:2rem;color:<?php echo $ql['color']; ?>;"></i>
                <span style="font-size:0.85rem;font-weight:600;text-align:center;"><?php echo $ql['label']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ── Recent Registrations Table ──────────────────────────── -->
<?php if (!empty($recentRegs)): ?>
<div class="admin-card">
    <div class="card-header">
        <h2>Recent Registrations</h2>
        <a href="<?php echo SITE_URL; ?>/admin/conference-registration" class="btn-admin btn-admin-secondary btn-sm">View All</a>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentRegs as $reg): ?>
                    <tr>
                        <td><strong style="color:#0D1B2A;"><?php echo e($reg['full_name']); ?></strong></td>
                        <td style="font-size:0.875rem;color:var(--text-secondary);"><?php echo e($reg['email']); ?></td>
                        <td>
                            <?php
                            $catColors = ['student'=>'badge-blue','professional'=>'badge-green','ingo'=>'badge-purple'];
                            $catLabels = ['student'=>'Student','professional'=>'Professional','ingo'=>'I/NGO'];
                            $cat = $reg['category'];
                            ?>
                            <span class="status-badge <?php echo $catColors[$cat] ?? 'badge-blue'; ?>">
                                <?php echo $catLabels[$cat] ?? ucfirst($cat); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $stColors = ['pending'=>'status-badge','approved'=>'status-badge active','declined'=>'status-badge inactive'];
                            $stLabels = ['pending'=>'Pending','approved'=>'Approved','declined'=>'Declined'];
                            $st = $reg['status'];
                            ?>
                            <span class="<?php echo $stColors[$st] ?? 'status-badge'; ?>">
                                <?php echo $stLabels[$st] ?? ucfirst($st); ?>
                            </span>
                        </td>
                        <td style="font-size:0.875rem;color:var(--text-secondary);">
                            <?php echo date('M d, Y', strtotime($reg['registered_at'])); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php endAdminLayout(); ?>
