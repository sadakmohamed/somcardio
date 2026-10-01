<?php
require_once __DIR__ . '/../includes/admin_layout.php';

$db = getDB();

function activeConferenceId(PDO $db): int {
    $row = $db->query("SELECT id FROM conferences WHERE status = 'active' ORDER BY year DESC, id DESC LIMIT 1")->fetch();
    return $row ? (int)$row['id'] : 0;
}

$confId = activeConferenceId($db);
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header('Location: ' . SITE_URL . '/admin/conference-abstracts');
        exit;
    }

    if (isset($_POST['save_abstract_settings'])) {
        $deadlineInput = trim($_POST['submission_deadline'] ?? '');
        $deadlineTimestamp = $deadlineInput !== '' ? strtotime(str_replace('T', ' ', $deadlineInput)) : false;
        $deadline = $deadlineTimestamp !== false ? date('Y-m-d H:i:s', $deadlineTimestamp) : null;
        $format = trim($_POST['format_requirements'] ?? '');
        $structure = trim($_POST['structure'] ?? '');
        $review = trim($_POST['review_process'] ?? '');

        if ($confId) {
            $stmt = $db->prepare(
                "INSERT INTO conference_abstract_settings (conference_id, submission_deadline, format_requirements, structure, review_process)
                 VALUES (:cid, :deadline, :format, :structure, :review)
                 ON DUPLICATE KEY UPDATE submission_deadline = VALUES(submission_deadline), format_requirements = VALUES(format_requirements), structure = VALUES(structure), review_process = VALUES(review_process)"
            );
            $stmt->execute([
                ':cid' => $confId,
                ':deadline' => $deadline,
                ':format' => $format,
                ':structure' => $structure,
                ':review' => $review,
            ]);
            setFlash('success', 'Abstract settings saved successfully.');
        } else {
            $error = 'Please create the active conference first.';
        }
        if ($error) {
            setFlash('error', $error);
        }
        header('Location: ' . SITE_URL . '/admin/conference-abstracts');
        exit;
    }

    if (isset($_POST['save_subtheme'])) {
        $title = trim($_POST['title'] ?? '');
        $detail = trim($_POST['detail'] ?? '');
        $order = (int)($_POST['display_order'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;

        if (!$confId) {
            $error = 'Please create the active conference first.';
        } elseif ($title === '') {
            $error = 'Subtheme title is required.';
        } else {
            if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
                $stmt = $db->prepare("UPDATE conference_subthemes SET title = :title, detail = :detail, display_order = :ord, is_active = :active WHERE id = :id");
                $stmt->execute([':title' => $title, ':detail' => $detail, ':ord' => $order, ':active' => $active, ':id' => (int)$_POST['id']]);
                setFlash('success', 'Subtheme updated successfully.');
            } else {
                $stmt = $db->prepare("INSERT INTO conference_subthemes (conference_id, title, detail, display_order, is_active) VALUES (:cid, :title, :detail, :ord, :active)");
                $stmt->execute([':cid' => $confId, ':title' => $title, ':detail' => $detail, ':ord' => $order, ':active' => $active]);
                setFlash('success', 'Subtheme added successfully.');
            }
        }
        if ($error) {
            setFlash('error', $error);
        }
        header('Location: ' . SITE_URL . '/admin/conference-abstracts');
        exit;
    }

    if (isset($_POST['save_criterion'])) {
        $title = trim($_POST['title'] ?? '');
        $detail = trim($_POST['detail'] ?? '');
        $order = (int)($_POST['display_order'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;

        if (!$confId) {
            $error = 'Please create the active conference first.';
        } elseif ($title === '') {
            $error = 'Evaluation criterion title is required.';
        } else {
            if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
                $stmt = $db->prepare("UPDATE conference_evaluation_criteria SET title = :title, detail = :detail, display_order = :ord, is_active = :active WHERE id = :id");
                $stmt->execute([':title' => $title, ':detail' => $detail, ':ord' => $order, ':active' => $active, ':id' => (int)$_POST['id']]);
                setFlash('success', 'Evaluation criterion updated successfully.');
            } else {
                $stmt = $db->prepare("INSERT INTO conference_evaluation_criteria (conference_id, title, detail, display_order, is_active) VALUES (:cid, :title, :detail, :ord, :active)");
                $stmt->execute([':cid' => $confId, ':title' => $title, ':detail' => $detail, ':ord' => $order, ':active' => $active]);
                setFlash('success', 'Evaluation criterion added successfully.');
            }
        }
        if ($error) {
            setFlash('error', $error);
        }
        header('Location: ' . SITE_URL . '/admin/conference-abstracts');
        exit;
    }
}

if ($action === 'delete_subtheme' && $id) {
    $db->prepare("DELETE FROM conference_subthemes WHERE id = :id")->execute([':id' => $id]);
    setFlash('success', 'Subtheme deleted.');
    header('Location: ' . SITE_URL . '/admin/conference-abstracts');
    exit;
}

if ($action === 'toggle_subtheme' && $id) {
    $current = $db->prepare("SELECT is_active FROM conference_subthemes WHERE id = :id");
    $current->execute([':id' => $id]);
    $currentValue = (int)$current->fetchColumn();
    $db->prepare("UPDATE conference_subthemes SET is_active = :val WHERE id = :id")->execute([':val' => $currentValue ? 0 : 1, ':id' => $id]);
    setFlash('success', 'Subtheme visibility updated.');
    header('Location: ' . SITE_URL . '/admin/conference-abstracts');
    exit;
}

if ($action === 'delete_criterion' && $id) {
    $db->prepare("DELETE FROM conference_evaluation_criteria WHERE id = :id")->execute([':id' => $id]);
    setFlash('success', 'Evaluation criterion deleted.');
    header('Location: ' . SITE_URL . '/admin/conference-abstracts');
    exit;
}

if ($action === 'toggle_criterion' && $id) {
    $current = $db->prepare("SELECT is_active FROM conference_evaluation_criteria WHERE id = :id");
    $current->execute([':id' => $id]);
    $currentValue = (int)$current->fetchColumn();
    $db->prepare("UPDATE conference_evaluation_criteria SET is_active = :val WHERE id = :id")->execute([':val' => $currentValue ? 0 : 1, ':id' => $id]);
    setFlash('success', 'Evaluation criterion visibility updated.');
    header('Location: ' . SITE_URL . '/admin/conference-abstracts');
    exit;
}

$settings = null;
if ($confId) {
    $settingsStmt = $db->prepare("SELECT * FROM conference_abstract_settings WHERE conference_id = :cid LIMIT 1");
    $settingsStmt->execute([':cid' => $confId]);
    $settings = $settingsStmt->fetch();
}

$subthemes = [];
if ($confId) {
    $subthemesStmt = $db->prepare("SELECT * FROM conference_subthemes WHERE conference_id = :cid ORDER BY display_order ASC, id ASC");
    $subthemesStmt->execute([':cid' => $confId]);
    $subthemes = $subthemesStmt->fetchAll();
}

$criteria = [];
if ($confId) {
    $criteriaStmt = $db->prepare("SELECT * FROM conference_evaluation_criteria WHERE conference_id = :cid ORDER BY display_order ASC, id ASC");
    $criteriaStmt->execute([':cid' => $confId]);
    $criteria = $criteriaStmt->fetchAll();
}

startAdminLayout('Abstract Settings');
?>

<div class="page-title-block">
    <div>
        <h1><i class="ph ph-file-text" style="color:#27AAE1;margin-right:10px;"></i>Abstract Settings</h1>
        <p>Configure the conference abstract submission page and scientific review structure.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" style="margin-bottom:20px;"><?php echo e($error); ?></div>
<?php endif; ?>

<div class="admin-card" style="margin-bottom:28px;">
    <div class="card-header"><h2>Abstract Submission Settings</h2></div>
    <div class="card-body">
        <form method="POST">
            <?php echo csrfField(); ?>
            <input type="hidden" name="save_abstract_settings" value="1">
            <div style="display:grid;grid-template-columns:1fr;gap:20px;">
                <div class="form-group">
                    <label class="form-label">Submission Deadline</label>
                    <input type="datetime-local" name="submission_deadline" class="form-control" value="<?php echo !empty($settings['submission_deadline']) ? date('Y-m-d\TH:i', strtotime($settings['submission_deadline'])) : ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Format Requirements</label>
                    <textarea name="format_requirements" class="form-control" rows="8"><?php echo e($settings['format_requirements'] ?? "<ul><li>Abstract length: 250–400 words</li><li>Use structured headings: Background, Methods, Results, Conclusion</li><li>Include 3–5 keywords and author affiliations</li><li>Submit as PDF in A4 format</li></ul>"); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Structure</label>
                    <textarea name="structure" class="form-control" rows="8"><?php echo e($settings['structure'] ?? "<ol><li>Title</li><li>Background</li><li>Methods</li><li>Results</li><li>Conclusion</li><li>Keywords</li></ol>"); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Review Process</label>
                    <textarea name="review_process" class="form-control" rows="8"><?php echo e($settings['review_process'] ?? "<ul><li>Initial technical screening</li><li>Peer review by the scientific committee</li><li>Notification of acceptance</li></ul>"); ?></textarea>
                </div>
            </div>
            <div style="margin-top:18px;">
                <button type="submit" class="btn-admin btn-admin-primary">Save Abstract Settings</button>
            </div>
        </form>
    </div>
</div>

<div class="admin-card" style="margin-bottom:28px;">
    <div class="card-header"><h2>Subthemes</h2></div>
    <div class="card-body">
        <form method="POST">
            <?php echo csrfField(); ?>
            <input type="hidden" name="save_subtheme" value="1">
            <div style="display:grid;grid-template-columns:1fr 1fr 120px 120px;gap:12px;align-items:end;">
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Cardiac Imaging">
                </div>
                <div class="form-group">
                    <label class="form-label">Detail</label>
                    <input type="text" name="detail" class="form-control" placeholder="Short note">
                </div>
                <div class="form-group">
                    <label class="form-label">Order</label>
                    <input type="number" name="display_order" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Active</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:10px;">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Visible</span>
                    </label>
                </div>
            </div>
            <div style="margin-top:14px;">
                <button type="submit" class="btn-admin btn-admin-primary">Add Subtheme</button>
            </div>
        </form>

        <?php if (!empty($subthemes)): ?>
            <table class="admin-table" style="margin-top:20px;">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Detail</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subthemes as $item): ?>
                        <tr>
                            <td><?php echo e($item['title']); ?></td>
                            <td><?php echo e($item['detail'] ?? ''); ?></td>
                            <td><?php echo (int)$item['display_order']; ?></td>
                            <td><?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?></td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/admin/conference-abstracts?action=toggle_subtheme&id=<?php echo (int)$item['id']; ?>" class="btn-admin btn-admin-secondary btn-sm"><?php echo $item['is_active'] ? 'Hide' : 'Show'; ?></a>
                                <a href="<?php echo SITE_URL; ?>/admin/conference-abstracts?action=delete_subtheme&id=<?php echo (int)$item['id']; ?>" class="btn-admin btn-admin-danger btn-sm confirm-delete" data-item="subtheme">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="admin-card" style="margin-bottom:28px;">
    <div class="card-header"><h2>Evaluation Criteria</h2></div>
    <div class="card-body">
        <form method="POST">
            <?php echo csrfField(); ?>
            <input type="hidden" name="save_criterion" value="1">
            <div style="display:grid;grid-template-columns:1fr 1fr 120px 120px;gap:12px;align-items:end;">
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Innovation and relevance">
                </div>
                <div class="form-group">
                    <label class="form-label">Detail</label>
                    <input type="text" name="detail" class="form-control" placeholder="Short explanation">
                </div>
                <div class="form-group">
                    <label class="form-label">Order</label>
                    <input type="number" name="display_order" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Active</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:10px;">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Visible</span>
                    </label>
                </div>
            </div>
            <div style="margin-top:14px;">
                <button type="submit" class="btn-admin btn-admin-primary">Add Criterion</button>
            </div>
        </form>

        <?php if (!empty($criteria)): ?>
            <table class="admin-table" style="margin-top:20px;">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Detail</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($criteria as $item): ?>
                        <tr>
                            <td><?php echo e($item['title']); ?></td>
                            <td><?php echo e($item['detail'] ?? ''); ?></td>
                            <td><?php echo (int)$item['display_order']; ?></td>
                            <td><?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?></td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/admin/conference-abstracts?action=toggle_criterion&id=<?php echo (int)$item['id']; ?>" class="btn-admin btn-admin-secondary btn-sm"><?php echo $item['is_active'] ? 'Hide' : 'Show'; ?></a>
                                <a href="<?php echo SITE_URL; ?>/admin/conference-abstracts?action=delete_criterion&id=<?php echo (int)$item['id']; ?>" class="btn-admin btn-admin-danger btn-sm confirm-delete" data-item="criterion">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php endAdminLayout(); ?>
