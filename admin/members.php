<?php
/**
 * Members Management — Somali Cardiac Society
 */
require_once __DIR__ . '/../includes/admin_layout.php';

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
        header('Location: members.php');
        exit;
    }

    if ($action === 'add' || $action === 'edit') {
        $fullName = trim($_POST['full_name'] ?? '');
        $experienceYears = (int)($_POST['experience_years'] ?? 0);
        $hospital = trim($_POST['hospital'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($fullName)) {
            $error = 'Full Name is required.';
        } else {
            // Photo upload
            $photoPath = $_POST['existing_photo'] ?? null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                // Delete existing photo if editing
                if ($action === 'edit' && $photoPath) {
                    deleteUploadedFile($photoPath);
                }
                
                $uploaded = handleImageUpload($_FILES['photo'], 'members');
                if ($uploaded) {
                    $photoPath = $uploaded;
                } else {
                    $error = 'Failed to upload photo. Only JPG, PNG, WEBP allowed (max 5MB).';
                }
            }

            if (!$error) {
                try {
                    if ($action === 'add') {
                        $stmt = $db->prepare("INSERT INTO members (full_name, photo, experience_years, hospital, specialization, bio, display_order, is_active) VALUES (:full_name, :photo, :experience_years, :hospital, :specialization, :bio, :display_order, :is_active)");
                        $stmt->execute([
                            ':full_name' => $fullName,
                            ':photo' => $photoPath,
                            ':experience_years' => $experienceYears,
                            ':hospital' => $hospital,
                            ':specialization' => $specialization,
                            ':bio' => $bio,
                            ':display_order' => $displayOrder,
                            ':is_active' => $isActive
                        ]);
                        setFlash('success', 'Member added successfully.');
                    } else {
                        $stmt = $db->prepare("UPDATE members SET full_name = :full_name, photo = :photo, experience_years = :experience_years, hospital = :hospital, specialization = :specialization, bio = :bio, display_order = :display_order, is_active = :is_active WHERE id = :id");
                        $stmt->execute([
                            ':full_name' => $fullName,
                            ':photo' => $photoPath,
                            ':experience_years' => $experienceYears,
                            ':hospital' => $hospital,
                            ':specialization' => $specialization,
                            ':bio' => $bio,
                            ':display_order' => $displayOrder,
                            ':is_active' => $isActive,
                            ':id' => $id
                        ]);
                        setFlash('success', 'Member updated successfully.');
                    }
                    header('Location: members.php');
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
    try {
        // Fetch current photo to delete from disk
        $stmt = $db->prepare("SELECT photo FROM members WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $photo = $stmt->fetchColumn();
        if ($photo) {
            deleteUploadedFile($photo);
        }

        $stmt = $db->prepare("DELETE FROM members WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Member deleted successfully.');
    } catch (Exception $ex) {
        setFlash('error', 'Failed to delete member.');
    }
    header('Location: members.php');
    exit;
}

// Display Views
if ($action === 'add' || $action === 'edit') {
    $member = ['full_name' => '', 'photo' => '', 'experience_years' => 0, 'hospital' => '', 'specialization' => '', 'bio' => '', 'display_order' => 0, 'is_active' => 1];
    if ($action === 'edit' && $id > 0) {
        $stmt = $db->prepare("SELECT * FROM members WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $member = $stmt->fetch();
        if (!$member) {
            setFlash('error', 'Member not found.');
            header('Location: members.php');
            exit;
        }
    }

    startAdminLayout(($action === 'add' ? 'Add New' : 'Edit') . ' Member');
    ?>
    <div class="page-title-block">
        <div>
            <h1><?php echo $action === 'add' ? 'Add New Member' : 'Edit Member Details'; ?></h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Fill in the profile details for the doctor/specialist.</p>
        </div>
        <a href="members.php" class="btn-admin btn-admin-secondary">Back to List</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="card-body">
            <form action="members.php?action=<?php echo $action; ?>&id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="admin-form">
                <?php echo csrfField(); ?>
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="existing_photo" value="<?php echo htmlspecialchars($member['photo'] ?? ''); ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($member['full_name']); ?>" placeholder="e.g. Dr. Ahmed Mohamed Hassan">
                    </div>

                    <div class="form-group">
                        <label for="specialization">Specialization / Role *</label>
                        <input type="text" id="specialization" name="specialization" required value="<?php echo htmlspecialchars($member['specialization']); ?>" placeholder="e.g. Interventional Cardiology">
                    </div>

                    <div class="form-group">
                        <label for="hospital">Working Hospital / Institution *</label>
                        <input type="text" id="hospital" name="hospital" required value="<?php echo htmlspecialchars($member['hospital']); ?>" placeholder="e.g. Mogadishu General Hospital">
                    </div>

                    <div class="form-group">
                        <label for="experience_years">Years of Experience</label>
                        <input type="number" id="experience_years" name="experience_years" min="0" value="<?php echo htmlspecialchars($member['experience_years']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" id="display_order" name="display_order" min="0" value="<?php echo htmlspecialchars($member['display_order']); ?>" placeholder="Lower numbers show first">
                    </div>

                    <div class="form-group" style="display: flex; align-items: flex-end; padding-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                            <input type="checkbox" name="is_active" value="1" <?php echo $member['is_active'] ? 'checked' : ''; ?>>
                            Visible on Public Directory
                        </label>
                    </div>

                    <div class="form-group full-width">
                        <label for="photo">Photo / Avatar</label>
                        <div class="file-upload-preview">
                            <div class="image-preview-box">
                                <img id="photoPreview" src="<?php echo $member['photo'] ? UPLOADS_URL . '/' . htmlspecialchars($member['photo']) : ''; ?>" alt="Preview" style="<?php echo $member['photo'] ? '' : 'display:none;'; ?>">
                                <?php if (!$member['photo']): ?>
                                    <span style="font-size: 1.5rem; color: var(--text-light);">📷</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <input type="file" id="photo" name="photo" class="image-upload-input" data-preview="photoPreview" accept="image/*">
                                <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">Recommended: Square ratio. Max file size: 5MB.</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="bio">Short Biography / Bio</label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Brief professional background summary..."><?php echo htmlspecialchars($member['bio'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="members.php" class="btn-admin btn-admin-secondary">Cancel</a>
                    <button type="submit" class="btn-admin btn-admin-primary">Save Member</button>
                </div>
            </form>
        </div>
    </div>
    <?php
    endAdminLayout();
} else {
    // List View
    try {
        $stmt = $db->query("SELECT * FROM members ORDER BY display_order ASC, full_name ASC");
        $membersList = $stmt->fetchAll();
    } catch (Exception $ex) {
        $membersList = [];
    }

    startAdminLayout('Manage Members');
    ?>
    <div class="page-title-block">
        <div>
            <h1>Manage Members Directory</h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Add, edit, or remove registered cardiac specialists.</p>
        </div>
        <a href="members.php?action=add" class="btn-admin btn-admin-primary">Add New Member</a>
    </div>

    <!-- Search Card -->
    <div class="admin-card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 15px 24px;">
            <input type="text" id="tableSearch" placeholder="Search members by name, hospital, specialization..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 0.9rem;">
        </div>
    </div>

    <div class="admin-card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="admin-table" id="dataTable">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Specialization</th>
                            <th>Hospital</th>
                            <th>Experience</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($membersList)): ?>
                            <?php foreach ($membersList as $m): ?>
                            <tr>
                                <td>
                                    <div class="user-info-cell">
                                        <?php if ($m['photo']): ?>
                                            <img src="<?php echo UPLOADS_URL . '/' . htmlspecialchars($m['photo']); ?>" alt="Photo" class="user-avatar-small">
                                        <?php else: ?>
                                            <div class="user-avatar-small"><?php echo strtoupper(substr($m['full_name'], 0, 1)); ?></div>
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($m['full_name']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($m['specialization']); ?></td>
                                <td><?php echo htmlspecialchars($m['hospital']); ?></td>
                                <td><?php echo $m['experience_years']; ?> Years</td>
                                <td><?php echo $m['display_order']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $m['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $m['is_active'] ? 'Active' : 'Hidden'; ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="members.php?action=edit&id=<?php echo $m['id']; ?>" class="btn-icon" title="Edit Profile">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    <a href="members.php?action=delete&id=<?php echo $m['id']; ?>" class="btn-icon btn-icon-danger confirm-delete" data-item="member" title="Delete Profile">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-light); padding: 50px;">No registered members found.</td>
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
