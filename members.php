<?php
/**
 * Members / Doctors Directory — Somali Cardiac Society
 */
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'Our Members';
$pageDescription = 'Meet the registered cardiologists and cardiac specialists of the Somali Cardiac Society.';

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM members WHERE is_active = 1 ORDER BY display_order ASC, full_name ASC");
    $members = $stmt->fetchAll();
} catch (Exception $ex) {
    $members = [];
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>Our Members</h1>
        <p>Meet the cardiac specialists and professionals advancing heart health in Somalia</p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <span style="color:rgba(255,255,255,0.8);">Members</span>
        </div>
    </div>
</div>

<!-- Members Grid -->
<section class="section">
    <div class="container">
        <?php if (!empty($members)): ?>
        <div class="section-header fade-in">
            <h2>Registered Specialists</h2>
            <p><?php echo count($members); ?> cardiac professionals committed to cardiovascular excellence</p>
            <div class="section-line"></div>
        </div>
        <div class="members-grid">
            <?php foreach ($members as $member): ?>
            <div class="member-card fade-in">
                <div class="member-avatar">
                    <?php if ($member['photo']): ?>
                        <img src="<?php echo UPLOADS_URL . '/' . e($member['photo']); ?>" alt="<?php echo e($member['full_name']); ?>">
                    <?php else: ?>
                        <svg width="50" height="50" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    <?php endif; ?>
                </div>
                <h3 class="member-name"><?php echo e($member['full_name']); ?></h3>
                <p class="member-spec"><?php echo e($member['specialization']); ?></p>
                <div class="member-exp">
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                    <?php echo e($member['experience_years']); ?>+ Years Experience
                </div>
                <p class="member-hospital">
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    <?php echo e($member['hospital']); ?>
                </p>
                <p class="member-bio"><?php echo e(substr($member['bio'] ?? '', 0, 150)); ?>...</p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:60px 20px;">
            <svg width="60" height="60" fill="var(--primary-blue)" viewBox="0 0 24 24" style="margin-bottom:20px;"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            <h3 style="margin-bottom:8px;">Members Directory Coming Soon</h3>
            <p style="color:var(--text-light);">Our member profiles are being updated. Please check back soon.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
