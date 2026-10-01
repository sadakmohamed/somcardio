<?php
/**
 * Conference Speakers — Admin CRUD
 * Somali Cardiac Society
 *
 * Manages speakers for the active/ongoing conference.
 * Actions: list, add, edit, toggle_active, delete
 */
require_once __DIR__ . '/../includes/admin_layout.php';

$db     = getDB();
$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error  = null;

/* ── Resolve active conference ID ─────────────────────────── */
function getActiveConfId(PDO $db): int {
    $stmt = $db->query(
        "SELECT id FROM conferences WHERE status = 'active' ORDER BY year DESC, id DESC LIMIT 1"
    );
    $row = $stmt->fetch();
    return $row ? (int)$row['id'] : 0;
}
$confId = getActiveConfId($db);

/* ── GET Actions ──────────────────────────────────────────── */
if ($action === 'toggle_active' && $id) {
    $stmt = $db->prepare("SELECT is_active FROM conference_speakers WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        $newVal = $row['is_active'] ? 0 : 1;
        $upd = $db->prepare("UPDATE conference_speakers SET is_active = :v WHERE id = :id");
        $upd->execute([':v' => $newVal, ':id' => $id]);
        setFlash('success', 'Speaker visibility updated.');
    }
    header('Location: ' . SITE_URL . '/admin/conference-speakers');
    exit;
}

if ($action === 'delete' && $id) {
    $stmt = $db->prepare("SELECT photo FROM conference_speakers WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        if ($row['photo']) deleteUploadedFile($row['photo']);
        $db->prepare("DELETE FROM conference_speakers WHERE id = :id")->execute([':id' => $id]);
        setFlash('success', 'Speaker deleted successfully.');
    }
    header('Location: ' . SITE_URL . '/admin/conference-speakers');
    exit;
}

/* ── POST Handler ──────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header('Location: ' . SITE_URL . '/admin/conference-speakers');
        exit;
    }

    $section      = in_array($_POST['section'] ?? '', ['welcome_ceremony', 'keynote']) ? $_POST['section'] : 'keynote';
    $fullName     = trim($_POST['full_name'] ?? '');
    $position     = trim($_POST['position'] ?? '');
    $isActive     = isset($_POST['is_active']) ? 1 : 0;
    $displayOrder = (int)($_POST['display_order'] ?? 0);

    if (empty($fullName)) {
        $error = 'Full Name is required.';
    } elseif (!$confId && $action === 'add') {
        $error = 'Create an active conference before adding speakers.';
    } else {
        // Photo upload
        $photo = ($action === 'edit' && $id) ? ($_POST['existing_photo'] ?? null) : null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploaded = handleImageUpload($_FILES['photo'], 'speakers');
            if ($uploaded) {
                if ($photo) deleteUploadedFile($photo);
                $photo = $uploaded;
            } else {
                $error = 'Photo upload failed. Only JPG, PNG, WEBP allowed (max 5 MB).';
            }
        }

        if (!$error) {
            if ($action === 'add') {
                $stmt = $db->prepare(
                    "INSERT INTO conference_speakers
                     (conference_id, section, full_name, position_title, photo, is_active, display_order)
                     VALUES (:cid, :section, :name, :pos, :photo, :active, :ord)"
                );
                $stmt->execute([
                    ':cid'     => $confId,
                    ':section' => $section,
                    ':name'    => $fullName,
                    ':pos'     => $position,
                    ':photo'   => $photo,
                    ':active'  => $isActive,
                    ':ord'     => $displayOrder,
                ]);
                setFlash('success', 'Speaker added successfully.');
            } else {
                $stmt = $db->prepare(
                    "UPDATE conference_speakers SET
                     section = :section, full_name = :name, position_title = :pos,
                     photo = :photo, is_active = :active, display_order = :ord
                     WHERE id = :id"
                );
                $stmt->execute([
                    ':section' => $section,
                    ':name'    => $fullName,
                    ':pos'     => $position,
                    ':photo'   => $photo,
                    ':active'  => $isActive,
                    ':ord'     => $displayOrder,
                    ':id'      => $id,
                ]);
                setFlash('success', 'Speaker updated successfully.');
            }
            header('Location: ' . SITE_URL . '/admin/conference-speakers');
            exit;
        }
    }
}

/* ── Load speaker for edit ─────────────────────────────────── */
$editSpeaker = null;
if (in_array($action, ['edit']) && $id) {
    $stmt = $db->prepare("SELECT * FROM conference_speakers WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $editSpeaker = $stmt->fetch();
    if (!$editSpeaker) {
        setFlash('error', 'Speaker not found.');
        header('Location: ' . SITE_URL . '/admin/conference-speakers');
        exit;
    }
}

/* ── Fetch all speakers ────────────────────────────────────── */
$speakers = [];
if ($confId) {
    $stmtList = $db->prepare(
        "SELECT * FROM conference_speakers
         WHERE conference_id = :cid
         ORDER BY section, display_order, id"
    );
    $stmtList->execute([':cid' => $confId]);
    $speakers = $stmtList->fetchAll();
} else {
    // Fallback: show all
    $speakers = $db->query(
        "SELECT * FROM conference_speakers ORDER BY section, display_order, id"
    )->fetchAll();
}

$sectionLabels = ['welcome_ceremony' => 'Welcome & Opening Ceremony', 'keynote' => 'Keynote Speakers'];

startAdminLayout('Conference Speakers');
?>

<!-- ── Page Title ─────────────────────────────────────────── -->
<div class="page-title-block">
    <div>
        <h1><i class="ph ph-microphone" style="color:#27AAE1;margin-right:10px;"></i>Conference Speakers</h1>
        <p>Manage speakers for the active conference. <?php echo $confId ? '' : '<span style="color:#ED1C24;">(No active conference found — please create one first.)</span>'; ?></p>
    </div>
    <div class="page-title-actions">
        <a href="<?php echo SITE_URL; ?>/admin/conference-speakers?action=add" class="btn-admin btn-admin-primary">
            <i class="ph ph-plus" style="font-size:1rem;"></i> Add Speaker
        </a>
    </div>
</div>

<!-- ── Add / Edit Form ─────────────────────────────────────── -->
<?php if ($action === 'add' || $action === 'edit'): ?>
<?php $formSpeaker = $editSpeaker ?? []; ?>
<div class="admin-card" style="margin-bottom:28px;">
    <div class="card-header">
        <h2><?php echo $action === 'edit' ? '<i class="ph ph-pencil-simple" style="margin-right:8px;"></i>Edit Speaker' : '<i class="ph ph-plus-circle" style="margin-right:8px;"></i>Add New Speaker'; ?></h2>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom:16px;"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <?php echo csrfField(); ?>
            <?php if ($action === 'edit' && $id): ?>
                <input type="hidden" name="existing_photo" value="<?php echo e($formSpeaker['photo'] ?? ''); ?>">
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                <!-- Section -->
                <div class="form-group">
                    <label class="form-label">Section <span style="color:#ED1C24;">*</span></label>
                    <select name="section" class="form-control">
                        <option value="welcome_ceremony" <?php echo in_array(($formSpeaker['section'] ?? ''), ['welcome', 'welcome_ceremony']) ? 'selected' : ''; ?>>Welcome &amp; Opening Ceremony</option>
                        <option value="keynote" <?php echo ($formSpeaker['section'] ?? 'keynote') === 'keynote' ? 'selected' : ''; ?>>Keynote Speakers</option>
                    </select>
                </div>

                <!-- Display Order -->
                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-control"
                           value="<?php echo (int)($formSpeaker['display_order'] ?? 0); ?>" min="0">
                </div>

                <!-- Full Name -->
                <div class="form-group">
                    <label class="form-label">Full Name <span style="color:#ED1C24;">*</span></label>
                    <input type="text" name="full_name" class="form-control" required
                           value="<?php echo e($formSpeaker['full_name'] ?? ''); ?>"
                           placeholder="Dr. Full Name">
                </div>

                <!-- Position -->
                <div class="form-group">
                    <label class="form-label">Position / Title</label>
                    <input type="text" name="position" class="form-control"
                           value="<?php echo e($formSpeaker['position_title'] ?? $formSpeaker['position'] ?? ''); ?>"
                           placeholder="e.g. Cardiologist, WHO Somalia">
                </div>

                <!-- Photo -->
                <div class="form-group">
                    <label class="form-label">Speaker Photo</label>
                    <?php if (!empty($formSpeaker['photo'])): ?>
                        <div style="margin-bottom:10px;">
                            <img id="speakerPhotoPreview"
                                 src="<?php echo UPLOADS_URL . '/' . e($formSpeaker['photo']); ?>"
                                 alt="Photo" style="width:90px;height:90px;object-fit:cover;border-radius:50%;border:3px solid #E2E8F0;">
                        </div>
                    <?php else: ?>
                        <img id="speakerPhotoPreview" src="" alt="" style="display:none;width:90px;height:90px;object-fit:cover;border-radius:50%;border:3px solid #E2E8F0;margin-bottom:10px;">
                    <?php endif; ?>
                    <input type="file" name="photo" class="form-control image-upload-input"
                           data-preview="speakerPhotoPreview" accept="image/*">
                    <small style="color:var(--text-light);">Square photo, JPG/PNG/WEBP, max 5MB</small>
                </div>

                <!-- Active -->
                <div class="form-group" style="display:flex;align-items:center;">
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-top:28px;">
                        <input type="checkbox" name="is_active" value="1"
                               <?php echo ($formSpeaker['is_active'] ?? 1) ? 'checked' : ''; ?>
                               style="width:20px;height:20px;accent-color:#27AAE1;">
                        <span style="font-weight:600;color:#0D1B2A;">Active / Visible on site</span>
                    </label>
                </div>

            </div>

            <div style="display:flex;gap:12px;margin-top:20px;">
                <a href="<?php echo SITE_URL; ?>/admin/conference-speakers" class="btn-admin btn-admin-secondary">Cancel</a>
                <button type="submit" class="btn-admin btn-admin-primary">
                    <i class="ph ph-floppy-disk" style="font-size:1rem;"></i>
                    <?php echo $action === 'edit' ? 'Update Speaker' : 'Add Speaker'; ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ── Speakers Table ──────────────────────────────────────── -->
<div class="admin-card">
    <div class="card-header">
        <h2>All Speakers (<?php echo count($speakers); ?>)</h2>
        <div style="display:flex;gap:10px;align-items:center;">
            <input type="text" id="tableSearch" class="form-control" style="width:220px;"
                   placeholder="Search speakers…">
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($speakers)): ?>
        <div style="text-align:center;padding:64px;color:var(--text-light);">
            <i class="ph ph-microphone" style="font-size:3rem;opacity:.3;display:block;margin-bottom:12px;"></i>
            No speakers yet. <a href="<?php echo SITE_URL; ?>/admin/conference-speakers?action=add" style="color:#27AAE1;">Add the first speaker</a>.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table" id="dataTable">
                <thead>
                    <tr>
                        <th style="width:64px;">Photo</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Section</th>
                        <th>Order</th>
                        <th>Active</th>
                        <th style="width:130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($speakers as $spk): ?>
                    <tr>
                        <!-- Photo Thumbnail -->
                        <td>
                            <?php if ($spk['photo']): ?>
                                <img src="<?php echo UPLOADS_URL . '/' . e($spk['photo']); ?>"
                                     alt="<?php echo e($spk['full_name']); ?>"
                                     style="width:48px;height:48px;object-fit:cover;border-radius:50%;border:2px solid #E2E8F0;">
                            <?php else: ?>
                                <div style="width:48px;height:48px;border-radius:50%;background:#F0F4F8;display:flex;align-items:center;justify-content:center;border:2px solid #E2E8F0;">
                                    <i class="ph ph-user" style="font-size:1.4rem;color:#94a3b8;"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <!-- Name -->
                        <td>
                            <strong style="color:#0D1B2A;"><?php echo e($spk['full_name']); ?></strong>
                        </td>
                        <!-- Position -->
                        <td style="color:var(--text-secondary);font-size:0.875rem;">
                            <?php echo e($spk['position_title'] ?? $spk['position'] ?? ''); ?>
                        </td>
                        <!-- Section Badge -->
                        <td>
                            <span class="status-badge <?php echo in_array($spk['section'], ['welcome', 'welcome_ceremony']) ? 'badge-purple' : 'badge-blue'; ?>">
                                <?php echo $sectionLabels[$spk['section']] ?? ucfirst($spk['section']); ?>
                            </span>
                        </td>
                        <!-- Order -->
                        <td style="text-align:center;color:var(--text-secondary);"><?php echo (int)$spk['display_order']; ?></td>
                        <!-- Active Toggle -->
                        <td>
                            <a href="<?php echo SITE_URL; ?>/admin/conference-speakers?action=toggle_active&id=<?php echo $spk['id']; ?>"
                               title="Toggle visibility"
                               style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;font-size:0.78rem;font-weight:600;text-decoration:none;
                               <?php echo $spk['is_active']
                                   ? 'background:rgba(16,185,129,0.1);color:#10b981;border:1px solid rgba(16,185,129,0.3);'
                                   : 'background:rgba(148,163,184,0.1);color:#94a3b8;border:1px solid #E2E8F0;'; ?>">
                                <i class="ph <?php echo $spk['is_active'] ? 'ph-eye' : 'ph-eye-slash'; ?>"></i>
                                <?php echo $spk['is_active'] ? 'Visible' : 'Hidden'; ?>
                            </a>
                        </td>
                        <!-- Actions -->
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="<?php echo SITE_URL; ?>/admin/conference-speakers?action=edit&id=<?php echo $spk['id']; ?>"
                                   class="btn-admin btn-admin-secondary btn-sm" title="Edit">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/conference-speakers?action=delete&id=<?php echo $spk['id']; ?>"
                                   class="btn-admin btn-sm confirm-delete"
                                   data-item="<?php echo e($spk['full_name']); ?>"
                                   style="background:rgba(237,28,36,0.08);color:#ED1C24;border:1px solid rgba(237,28,36,0.2);"
                                   title="Delete">
                                    <i class="ph ph-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php endAdminLayout(); ?>
