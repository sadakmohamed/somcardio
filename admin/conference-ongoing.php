<?php
/**
 * Ongoing Conference — Admin Edit Page
 * Somali Cardiac Society
 *
 * Manages the single active conference record.
 * Creates one if none exists. Handles rich text, image uploads,
 * and JSON-encoded "who_should_attend" checkboxes.
 */
require_once __DIR__ . '/../includes/admin_layout.php';

$db = getDB();

/* ── Helper: fetch or create default conference ────────────── */
function getOrCreateConference(PDO $db): array {
    $stmt = $db->query(
        "SELECT * FROM conferences ORDER BY (status = 'active') DESC, year DESC, id DESC LIMIT 1"
    );
    $row = $stmt->fetch();
    if (!$row) {
        $year = (int)date('Y');
        $title = 'SCS Annual Conference';
        $insert = $db->prepare(
            "INSERT INTO conferences (title, slug, year, status)
             VALUES (:title, :slug, :year, 'active')"
        );
        $insert->execute([
            ':title' => $title,
            ':slug' => generateSlug($title . ' ' . $year),
            ':year' => $year,
        ]);
        $row = $db->query(
            "SELECT * FROM conferences ORDER BY (status = 'active') DESC, year DESC, id DESC LIMIT 1"
        )->fetch();
    }
    return $row;
}

$conf  = getOrCreateConference($db);
$confId = (int)$conf['id'];
$error = null;

/* ── POST Handler ──────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token. Please try again.');
        header('Location: ' . SITE_URL . '/admin/conference-ongoing');
        exit;
    }

    // Collect fields
    $title       = trim($_POST['title'] ?? '');
    $year        = (int)($_POST['conference_year'] ?? date('Y'));
    $status      = ($_POST['status'] ?? 'active') === 'active' ? 'active' : 'inactive';
    $heroText    = trim($_POST['hero_text'] ?? '');
    $bgText      = trim($_POST['background_text'] ?? '');
    $headName    = trim($_POST['head_name'] ?? '');
    $headMsg     = trim($_POST['head_message'] ?? '');
    $objectives  = trim($_POST['objectives'] ?? '');
    $whoRaw      = $_POST['who_should_attend'] ?? [];
    $whoJson     = json_encode(array_values($whoRaw));

    if (empty($title)) {
        $error = 'Conference Title is required.';
    } else {

        // Hero image upload
        $heroImage = $conf['hero_image'];
        if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = handleImageUpload($_FILES['hero_image'], 'conference');
            if ($uploaded) {
                if ($heroImage) deleteUploadedFile($heroImage);
                $heroImage = $uploaded;
            } else {
                $error = 'Hero image upload failed. Only JPG, PNG, WEBP allowed (max 5 MB).';
            }
        }

        // Head photo upload
        $headPhoto = $conf['head_photo'];
        if (!$error && isset($_FILES['head_photo']) && $_FILES['head_photo']['error'] === UPLOAD_ERR_OK) {
            $uploaded = handleImageUpload($_FILES['head_photo'], 'conference');
            if ($uploaded) {
                if ($headPhoto) deleteUploadedFile($headPhoto);
                $headPhoto = $uploaded;
            } else {
                $error = 'Head photo upload failed. Only JPG, PNG, WEBP allowed (max 5 MB).';
            }
        }

        if (!$error) {
            $stmt = $db->prepare("UPDATE conferences SET
                title = :title,
                year = :year,
                status = :status,
                hero_image = :hero_image,
                hero_text = :hero_text,
                background_text = :bg_text,
                head_name = :head_name,
                head_photo = :head_photo,
                head_message = :head_message,
                objectives = :objectives,
                who_should_attend = :who
                WHERE id = :id");
            $stmt->execute([
                ':title'      => $title,
                ':year'       => $year,
                ':status'     => $status,
                ':hero_image' => $heroImage,
                ':hero_text'  => $heroText,
                ':bg_text'    => $bgText,
                ':head_name'  => $headName,
                ':head_photo' => $headPhoto,
                ':head_message' => $headMsg,
                ':objectives' => $objectives,
                ':who'        => $whoJson,
                ':id'         => $confId,
            ]);

            setFlash('success', 'Conference updated successfully.');
            header('Location: ' . SITE_URL . '/admin/conference-ongoing');
            exit;
        }
    }

    // Re-fetch on error to keep fresh data
    $conf = getOrCreateConference($db);
}

// Parse who_should_attend JSON
$whoChecked = [];
if (!empty($conf['who_should_attend'])) {
    $decoded = json_decode($conf['who_should_attend'], true);
    if (is_array($decoded)) $whoChecked = $decoded;
}

$whoOptions = [
    'Civil Society', 'Private Sector', 'Students', 'International Partners',
    'NGOs', 'Universities', 'Researchers', 'Veterinarian',
    'Health Professionals', 'Government Officials',
];

startAdminLayout('Ongoing Conference');
?>

<!-- ── Page Title ─────────────────────────────────────────── -->
<div class="page-title-block">
    <div>
        <h1><i class="ph ph-calendar" style="color:#27AAE1;margin-right:10px;"></i>Ongoing Conference</h1>
        <p>Edit all details for the current active conference.</p>
    </div>
    <div class="page-title-actions">
        <a href="<?php echo SITE_URL; ?>/admin/conference" class="btn-admin btn-admin-secondary">
            <i class="ph ph-arrow-left" style="font-size:1rem;"></i> Back to Hub
        </a>
    </div>
</div>

<?php if ($error): ?>
<div class="alert alert-error" style="margin-bottom:20px;"><?php echo e($error); ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="confForm">
    <?php echo csrfField(); ?>

    <!-- ── Section 1: Basic Info ─────────────────────────────── -->
    <div class="admin-card" style="margin-bottom:24px;">
        <div class="card-header"><h2><i class="ph ph-info" style="margin-right:8px;"></i>Basic Information</h2></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                <!-- Title -->
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Conference Title <span style="color:#ED1C24;">*</span></label>
                    <input type="text" name="title" class="form-control"
                           value="<?php echo e($conf['title']); ?>" required
                           placeholder="e.g. SCS Annual International Conference 2026">
                </div>

                <!-- Year -->
                <div class="form-group">
                    <label class="form-label">Conference Year</label>
                    <input type="number" name="conference_year" class="form-control"
                           value="<?php echo e($conf['year']); ?>"
                           min="2000" max="2100" placeholder="<?php echo date('Y'); ?>">
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="active"   <?php echo $conf['status']==='active'   ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $conf['status']==='inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Section 2: Hero / Banner ──────────────────────────── -->
    <div class="admin-card" style="margin-bottom:24px;">
        <div class="card-header"><h2><i class="ph ph-image" style="margin-right:8px;"></i>Hero / Banner</h2></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                <!-- Hero Image -->
                <div class="form-group">
                    <label class="form-label">Hero Image (16:9)</label>
                    <?php if ($conf['hero_image']): ?>
                        <div style="margin-bottom:10px;">
                            <img id="heroImagePreview"
                                 src="<?php echo UPLOADS_URL . '/' . e($conf['hero_image']); ?>"
                                 alt="Hero" style="max-width:100%;height:140px;object-fit:cover;border-radius:10px;border:2px solid #E2E8F0;">
                        </div>
                    <?php else: ?>
                        <img id="heroImagePreview" src="" alt="" style="display:none;max-width:100%;height:140px;object-fit:cover;border-radius:10px;border:2px solid #E2E8F0;margin-bottom:10px;">
                    <?php endif; ?>
                    <input type="file" name="hero_image" class="form-control image-upload-input"
                           data-crop-ratio="1.7778" data-preview="heroImagePreview" accept="image/*">
                    <small style="color:var(--text-light);">Recommended: 1920×1080px, JPG/PNG/WEBP, max 5MB</small>
                </div>

                <!-- Hero Text -->
                <div class="form-group">
                    <label class="form-label">Hero Tagline / Text</label>
                    <textarea name="hero_text" class="form-control" rows="5"
                              placeholder="Short tagline displayed over the hero image..."><?php echo e($conf['hero_text']); ?></textarea>
                </div>

            </div>
        </div>
    </div>

    <!-- ── Section 3: Conference Background ──────────────────── -->
    <div class="admin-card" style="margin-bottom:24px;">
        <div class="card-header"><h2><i class="ph ph-book-open" style="margin-right:8px;"></i>Conference Background</h2></div>
        <div class="card-body">
            <label class="form-label">Background / About the Conference</label>
            <div id="backgroundEditor" data-placeholder="Write the conference background and context..." style="min-height:200px;"></div>
            <textarea name="background_text" id="background_text" style="display:none;"><?php echo $conf['background_text']; ?></textarea>
        </div>
    </div>

    <!-- ── Section 4: Objectives ─────────────────────────────── -->
    <div class="admin-card" style="margin-bottom:24px;">
        <div class="card-header"><h2><i class="ph ph-target" style="margin-right:8px;"></i>Conference Objectives</h2></div>
        <div class="card-body">
            <label class="form-label">Objectives</label>
            <div id="objectivesEditor" data-placeholder="List the goals and objectives of the conference..." style="min-height:180px;"></div>
            <textarea name="objectives" id="objectives" style="display:none;"><?php echo $conf['objectives']; ?></textarea>
        </div>
    </div>

    <!-- ── Section 5: Head of Conference ─────────────────────── -->
    <div class="admin-card" style="margin-bottom:24px;">
        <div class="card-header"><h2><i class="ph ph-user-circle" style="margin-right:8px;"></i>Head of Conference</h2></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                <!-- Head Name -->
                <div class="form-group">
                    <label class="form-label">Head of Conference Name</label>
                    <input type="text" name="head_name" class="form-control"
                           value="<?php echo e($conf['head_name']); ?>"
                           placeholder="Dr. Full Name">
                </div>

                <!-- Head Photo -->
                <div class="form-group">
                    <label class="form-label">Head of Conference Photo</label>
                    <?php if ($conf['head_photo']): ?>
                        <div style="margin-bottom:10px;">
                            <img id="headPhotoPreview"
                                 src="<?php echo UPLOADS_URL . '/' . e($conf['head_photo']); ?>"
                                 alt="Head Photo" style="width:100px;height:100px;object-fit:cover;border-radius:50%;border:3px solid #E2E8F0;">
                        </div>
                    <?php else: ?>
                        <img id="headPhotoPreview" src="" alt="" style="display:none;width:100px;height:100px;object-fit:cover;border-radius:50%;border:3px solid #E2E8F0;margin-bottom:10px;">
                    <?php endif; ?>
                    <input type="file" name="head_photo" class="form-control image-upload-input"
                           data-preview="headPhotoPreview" accept="image/*">
                    <small style="color:var(--text-light);">Square photo preferred, JPG/PNG/WEBP</small>
                </div>

                <!-- Head Message (full width) -->
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Head of Conference Message</label>
                    <div id="headMessageEditor" data-placeholder="Message from the Head of Conference..." style="min-height:200px;"></div>
                    <textarea name="head_message" id="head_message" style="display:none;"><?php echo $conf['head_message']; ?></textarea>
                </div>

            </div>
        </div>
    </div>

    <!-- ── Section 6: Who Should Attend ──────────────────────── -->
    <div class="admin-card" style="margin-bottom:24px;">
        <div class="card-header"><h2><i class="ph ph-users-three" style="margin-right:8px;"></i>Who Should Attend</h2></div>
        <div class="card-body">
            <label class="form-label" style="margin-bottom:14px;display:block;">Select all applicable groups:</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;">
                <?php foreach ($whoOptions as $opt): ?>
                <label style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;border:1.5px solid #E2E8F0;cursor:pointer;transition:all .15s;"
                       onmouseover="this.style.borderColor='#27AAE1';this.style.background='rgba(39,170,225,0.04)';"
                       onmouseout="this.style.borderColor=this.querySelector('input').checked?'#27AAE1':'#E2E8F0';this.style.background=this.querySelector('input').checked?'rgba(39,170,225,0.06)':'';">
                    <input type="checkbox" name="who_should_attend[]" value="<?php echo e($opt); ?>"
                           <?php echo in_array($opt, $whoChecked) ? 'checked' : ''; ?>
                           style="width:18px;height:18px;accent-color:#27AAE1;cursor:pointer;">
                    <span style="font-size:0.9rem;font-weight:500;color:#0D1B2A;"><?php echo e($opt); ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── Submit ─────────────────────────────────────────────── -->
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:40px;">
        <a href="<?php echo SITE_URL; ?>/admin/conference" class="btn-admin btn-admin-secondary">Cancel</a>
        <button type="submit" class="btn-admin btn-admin-primary">
            <i class="ph ph-floppy-disk" style="font-size:1rem;"></i> Save Conference
        </button>
    </div>

</form>

<?php endAdminLayout(); ?>
