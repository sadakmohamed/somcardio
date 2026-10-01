<?php
require_once __DIR__ . '/../includes/admin_layout.php';

$db = getDB();
$conf = $db->query("SELECT * FROM conferences WHERE status = 'active' ORDER BY year DESC, id DESC LIMIT 1")->fetch();
$confId = $conf ? (int)$conf['id'] : 0;
$filter = $_GET['filter'] ?? 'all';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header('Location: ' . SITE_URL . '/admin/conference-registration');
        exit;
    }

    if (!$confId) {
        setFlash('error', 'Please create an ongoing conference first.');
        header('Location: ' . SITE_URL . '/admin/conference-registration');
        exit;
    }

    $process = trim($_POST['process_text'] ?? '');
    $account = trim($_POST['account_number'] ?? '');
    $bank = trim($_POST['bank_name'] ?? '');
    $holder = trim($_POST['account_holder'] ?? '');

    $stmt = $db->prepare(
        "INSERT INTO conference_registration_settings (conference_id, process_text, account_number, bank_name, account_holder)
         VALUES (:cid, :process, :account, :bank, :holder)
         ON DUPLICATE KEY UPDATE process_text = VALUES(process_text), account_number = VALUES(account_number), bank_name = VALUES(bank_name), account_holder = VALUES(account_holder)"
    );
    $stmt->execute([
        ':cid' => $confId,
        ':process' => $process,
        ':account' => $account,
        ':bank' => $bank,
        ':holder' => $holder,
    ]);
    setFlash('success', 'Registration settings updated successfully.');
    header('Location: ' . SITE_URL . '/admin/conference-registration');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'update_status') {
    $id = (int)($_GET['id'] ?? 0);
    $status = in_array($_GET['status'] ?? '', ['pending','approved','declined']) ? $_GET['status'] : 'pending';
    $db->prepare("UPDATE conference_registrations SET status = :status WHERE id = :id")->execute([':status' => $status, ':id' => $id]);
    setFlash('success', 'Registration status updated.');
    header('Location: ' . SITE_URL . '/admin/conference-registration');
    exit;
}

$settings = null;
if ($confId) {
    $settingStmt = $db->prepare("SELECT * FROM conference_registration_settings WHERE conference_id = :cid LIMIT 1");
    $settingStmt->execute([':cid' => $confId]);
    $settings = $settingStmt->fetch();
}

$where = $confId ? "WHERE conference_id = :cid" : "WHERE 1=0";
if ($filter !== 'all') {
    $where .= " AND status = :filter";
}

$stmt = $db->prepare("SELECT * FROM conference_registrations $where ORDER BY registered_at DESC");
$params = [];
if ($confId) {
    $params[':cid'] = $confId;
}
if ($filter !== 'all') {
    $params[':filter'] = $filter;
}
$stmt->execute($params);
$registrations = $stmt->fetchAll();

startAdminLayout('Conference Registrations');
?>

<div class="page-title-block">
    <div>
        <h1><i class="ph ph-users" style="color:#27AAE1;margin-right:10px;"></i>Conference Registrations</h1>
        <p>Manage payment settings and review conference registrations.</p>
    </div>
</div>

<div class="admin-card" style="margin-bottom:28px;">
    <div class="card-header"><h2>Registration Payment Settings</h2></div>
    <div class="card-body">
        <form method="POST">
            <?php echo csrfField(); ?>
            <input type="hidden" name="save_settings" value="1">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div class="form-group">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" value="<?php echo e($settings['bank_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="account_number" class="form-control" value="<?php echo e($settings['account_number'] ?? ''); ?>">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Account Holder</label>
                    <input type="text" name="account_holder" class="form-control" value="<?php echo e($settings['account_holder'] ?? ''); ?>">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Registration Process Text</label>
                    <textarea name="process_text" rows="6" class="form-control"><?php echo e($settings['process_text'] ?? 'Please pay the conference fee using the details above and upload the payment confirmation.'); ?></textarea>
                </div>
            </div>
            <div style="margin-top:18px;">
                <button type="submit" class="btn-admin btn-admin-primary">Save Registration Settings</button>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h2>Registrations</h2>
        <div style="display:flex;gap:8px;">
            <a href="<?php echo SITE_URL; ?>/admin/conference-registration?filter=all" class="btn-admin btn-admin-secondary btn-sm">All</a>
            <a href="<?php echo SITE_URL; ?>/admin/conference-registration?filter=pending" class="btn-admin btn-admin-secondary btn-sm">Pending</a>
            <a href="<?php echo SITE_URL; ?>/admin/conference-registration?filter=approved" class="btn-admin btn-admin-secondary btn-sm">Approved</a>
            <a href="<?php echo SITE_URL; ?>/admin/conference-registration?filter=declined" class="btn-admin btn-admin-secondary btn-sm">Declined</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($registrations)): ?>
            <table class="admin-table" id="dataTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Category</th>
                        <th>Country</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $reg): ?>
                        <tr>
                            <td><?php echo e(trim(($reg['first_name'] ?? '') . ' ' . ($reg['last_name'] ?? '')) ?: ($reg['full_name'] ?? '')); ?></td>
                            <td><?php echo e($reg['email']); ?></td>
                            <td><?php echo e(ucfirst($reg['category'])); ?></td>
                            <td><?php echo e($reg['country']); ?></td>
                            <td>
                                <span style="font-weight:700; color:<?php echo $reg['status']==='approved' ? '#10b981' : ($reg['status']==='declined' ? '#ED1C24' : '#f59e0b'); ?>;">
                                    <?php echo ucfirst($reg['status']); ?>
                                </span>
                            </td>
                            <td><?php echo e(date('M d, Y', strtotime($reg['registered_at']))); ?></td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/admin/conference-registration?action=update_status&id=<?php echo (int)$reg['id']; ?>&status=approved" class="btn-admin btn-admin-primary btn-sm">Approve</a>
                                <a href="<?php echo SITE_URL; ?>/admin/conference-registration?action=update_status&id=<?php echo (int)$reg['id']; ?>&status=declined" class="btn-admin btn-admin-danger btn-sm">Decline</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No registrations found for the current filter.</p>
        <?php endif; ?>
    </div>
</div>

<?php endAdminLayout(); ?>
