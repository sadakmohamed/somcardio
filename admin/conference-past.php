<?php
require_once __DIR__ . '/../includes/admin_layout.php';

$db = getDB();
$maxGalleryImages = 10;
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Your session security token expired. Please try again.');
        header('Location: ' . SITE_URL . '/admin/conference-past');
        exit;
    }

    $mode = $_POST['mode'] ?? 'save';
    $id = (int)($_POST['id'] ?? 0);

    if ($mode === 'delete' && $id > 0) {
        $imageStmt = $db->prepare('SELECT image_path FROM past_conference_gallery WHERE past_conference_id = :id');
        $imageStmt->execute([':id' => $id]);
        $imagePaths = $imageStmt->fetchAll(PDO::FETCH_COLUMN);
        $db->prepare('DELETE FROM past_conferences WHERE id = :id')->execute([':id' => $id]);
        foreach ($imagePaths as $imagePath) {
            deleteUploadedFile($imagePath);
        }
        setFlash('success', 'Archived conference and its gallery were deleted.');
        header('Location: ' . SITE_URL . '/admin/conference-past');
        exit;
    }

    if ($mode === 'save') {
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $dateInput = trim($_POST['conference_date'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $removeIds = array_values(array_unique(array_filter(array_map('intval', $_POST['remove_images'] ?? []))));
        $newImagePaths = [];
        $removedImagePaths = [];

        $date = null;
        if ($dateInput !== '') {
            $parsedDate = DateTime::createFromFormat('!Y-m-d', $dateInput);
            if ($parsedDate && $parsedDate->format('Y-m-d') === $dateInput) {
                $date = $dateInput;
            } else {
                $error = 'Enter a valid conference date.';
            }
        }

        if (!$error && $title === '') {
            $error = 'Conference name is required.';
        }
        if (!$error && $body === '') {
            $error = 'Add a short conference description before saving.';
        }

        $currentItem = null;
        $currentImageCount = 0;
        if (!$error && $id > 0) {
            $itemStmt = $db->prepare('SELECT * FROM past_conferences WHERE id = :id LIMIT 1');
            $itemStmt->execute([':id' => $id]);
            $currentItem = $itemStmt->fetch();
            if (!$currentItem) {
                $error = 'The archive entry could not be found.';
            } else {
                $countStmt = $db->prepare('SELECT COUNT(*) FROM past_conference_gallery WHERE past_conference_id = :id');
                $countStmt->execute([':id' => $id]);
                $currentImageCount = (int)$countStmt->fetchColumn();
            }
        }

        $validRemoveIds = [];
        if (!$error && $id > 0 && $removeIds) {
            $placeholders = implode(',', array_fill(0, count($removeIds), '?'));
            $removeStmt = $db->prepare("SELECT id, image_path FROM past_conference_gallery WHERE past_conference_id = ? AND id IN ($placeholders)");
            $removeStmt->execute(array_merge([$id], $removeIds));
            foreach ($removeStmt->fetchAll() as $removeRow) {
                $validRemoveIds[] = (int)$removeRow['id'];
                $removedImagePaths[] = $removeRow['image_path'];
            }
        }

        $uploadCount = 0;
        if (isset($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
            foreach ($_FILES['gallery_images']['name'] as $fileName) {
                if ($fileName !== '') {
                    $uploadCount++;
                }
            }
        }
        $remainingSlots = $maxGalleryImages - ($currentImageCount - count($validRemoveIds));
        if (!$error && $uploadCount > $remainingSlots) {
            $error = 'This conference can have up to 10 photos. Remove ' . ($uploadCount - $remainingSlots) . ' image(s) or choose fewer files.';
        }

        if (!$error && $uploadCount > 0) {
            $allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            foreach ($_FILES['gallery_images']['name'] as $index => $fileName) {
                if ($fileName === '') {
                    continue;
                }
                $uploadError = $_FILES['gallery_images']['error'][$index] ?? UPLOAD_ERR_NO_FILE;
                if ($uploadError !== UPLOAD_ERR_OK) {
                    $error = 'One of the selected photos could not be uploaded.';
                    break;
                }
                $temporaryPath = $_FILES['gallery_images']['tmp_name'][$index] ?? '';
                $imageInfo = $temporaryPath !== '' ? @getimagesize($temporaryPath) : false;
                if (!$imageInfo || !in_array($imageInfo['mime'] ?? '', $allowedImageTypes, true)) {
                    $error = 'Gallery files must be valid JPG, PNG, WEBP, or GIF images.';
                    break;
                }
                if ((int)($_FILES['gallery_images']['size'][$index] ?? 0) > 5 * 1024 * 1024) {
                    $error = 'Each gallery image must be 5 MB or smaller.';
                    break;
                }

                $file = [
                    'name' => $fileName,
                    'type' => $imageInfo['mime'],
                    'tmp_name' => $temporaryPath,
                    'error' => $uploadError,
                    'size' => (int)($_FILES['gallery_images']['size'][$index] ?? 0),
                ];
                $uploadedPath = handleImageUpload($file, 'past-conferences');
                if (!$uploadedPath) {
                    $error = 'A gallery image could not be saved. Please try again.';
                    break;
                }
                $newImagePaths[] = $uploadedPath;
            }
        }

        if (!$error) {
            try {
                $db->beginTransaction();
                if ($id > 0) {
                    $slug = $currentItem['slug'];
                    $updateStmt = $db->prepare('UPDATE past_conferences SET title = :title, body = :body, conference_date = :date, is_active = :active WHERE id = :id');
                    $updateStmt->execute([':title' => $title, ':body' => $body, ':date' => $date, ':active' => $isActive, ':id' => $id]);
                } else {
                    $baseSlug = generateSlug($title) ?: 'conference';
                    $slug = $baseSlug;
                    $suffix = 2;
                    $slugStmt = $db->prepare('SELECT COUNT(*) FROM past_conferences WHERE slug = :slug');
                    do {
                        $slugStmt->execute([':slug' => $slug]);
                        if (!(int)$slugStmt->fetchColumn()) {
                            break;
                        }
                        $slug = $baseSlug . '-' . $suffix++;
                    } while (true);

                    $insertStmt = $db->prepare('INSERT INTO past_conferences (title, slug, body, conference_date, is_active) VALUES (:title, :slug, :body, :date, :active)');
                    $insertStmt->execute([':title' => $title, ':slug' => $slug, ':body' => $body, ':date' => $date, ':active' => $isActive]);
                    $id = (int)$db->lastInsertId();
                }

                if ($validRemoveIds) {
                    $placeholders = implode(',', array_fill(0, count($validRemoveIds), '?'));
                    $deleteStmt = $db->prepare("DELETE FROM past_conference_gallery WHERE past_conference_id = ? AND id IN ($placeholders)");
                    $deleteStmt->execute(array_merge([$id], $validRemoveIds));
                }

                $countStmt = $db->prepare('SELECT COUNT(*) FROM past_conference_gallery WHERE past_conference_id = :id');
                $countStmt->execute([':id' => $id]);
                $displayOrder = (int)$countStmt->fetchColumn();
                $galleryInsert = $db->prepare('INSERT INTO past_conference_gallery (past_conference_id, image_path, display_order) VALUES (:id, :path, :order)');
                foreach ($newImagePaths as $imagePath) {
                    $galleryInsert->execute([':id' => $id, ':path' => $imagePath, ':order' => $displayOrder++]);
                }
                $db->commit();

                foreach ($removedImagePaths as $imagePath) {
                    deleteUploadedFile($imagePath);
                }
                setFlash('success', $uploadCount > 0 || $validRemoveIds ? 'Conference archive and gallery saved.' : 'Conference archive saved.');
                header('Location: ' . SITE_URL . '/admin/conference-past?edit=' . $id);
                exit;
            } catch (Throwable $exception) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                foreach ($newImagePaths as $imagePath) {
                    deleteUploadedFile($imagePath);
                }
                error_log('Past conference save failed: ' . $exception->getMessage());
                $error = 'The conference could not be saved. Please try again.';
            }
        }

        foreach ($newImagePaths as $imagePath) {
            deleteUploadedFile($imagePath);
        }
        setFlash('error', $error ?: 'The conference could not be saved.');
        header('Location: ' . SITE_URL . '/admin/conference-past' . ($id > 0 ? '?edit=' . $id : '?action=add'));
        exit;
    }
}

$editItem = null;
$editImages = [];
if ($editId > 0) {
    $editStmt = $db->prepare('SELECT * FROM past_conferences WHERE id = :id LIMIT 1');
    $editStmt->execute([':id' => $editId]);
    $editItem = $editStmt->fetch();
    if ($editItem) {
        $imagesStmt = $db->prepare('SELECT * FROM past_conference_gallery WHERE past_conference_id = :id ORDER BY display_order ASC, id ASC');
        $imagesStmt->execute([':id' => $editId]);
        $editImages = $imagesStmt->fetchAll();
    }
}

$items = $db->query(
    'SELECT p.*, COUNT(g.id) AS image_count FROM past_conferences p LEFT JOIN past_conference_gallery g ON g.past_conference_id = p.id GROUP BY p.id ORDER BY p.conference_date DESC, p.id DESC'
)->fetchAll();
$totalImages = (int)$db->query('SELECT COUNT(*) FROM past_conference_gallery')->fetchColumn();
$visibleCount = (int)$db->query('SELECT COUNT(*) FROM past_conferences WHERE is_active = 1')->fetchColumn();
$showForm = isset($_GET['action']) && $_GET['action'] === 'add' || $editItem;

startAdminLayout('Past Conferences');
?>

<div class="page-title-block past-admin-heading">
    <div>
        <span class="past-admin-eyebrow">CONFERENCE ARCHIVE</span>
        <h1><i class="ph ph-images" aria-hidden="true"></i> Past conferences</h1>
        <p>Build a clean archive of past SCS gatherings with a short story and up to 10 photos each.</p>
    </div>
    <?php if (!$showForm): ?><a href="<?php echo SITE_URL; ?>/admin/conference-past?action=add" class="btn-admin btn-admin-primary"><i class="ph ph-plus" aria-hidden="true"></i> Add conference</a><?php endif; ?>
</div>

<div class="past-admin-stats">
    <div><span>ARCHIVE ENTRIES</span><strong><?php echo count($items); ?></strong></div>
    <div><span>VISIBLE ON WEBSITE</span><strong><?php echo $visibleCount; ?></strong></div>
    <div><span>GALLERY PHOTOS</span><strong><?php echo $totalImages; ?></strong></div>
</div>

<?php if ($showForm): ?>
    <section class="admin-card past-admin-editor">
        <div class="card-header"><div><span class="past-admin-eyebrow"><?php echo $editItem ? 'EDIT ARCHIVE ENTRY' : 'NEW ARCHIVE ENTRY'; ?></span><h2><?php echo $editItem ? 'Update conference' : 'Add past conference'; ?></h2></div><a class="btn-admin btn-admin-secondary btn-sm" href="<?php echo SITE_URL; ?>/admin/conference-past">Back to archive</a></div>
        <div class="card-body">
            <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
            <?php if ($editItem && $editItem['is_active']): ?><div class="past-admin-hint"><i class="ph ph-info" aria-hidden="true"></i> This entry is visible on the public conference archive.</div><?php endif; ?>
            <form method="POST" enctype="multipart/form-data" class="past-admin-form" id="pastConferenceForm">
                <?php echo csrfField(); ?>
                <input type="hidden" name="mode" value="save">
                <input type="hidden" name="id" value="<?php echo (int)($editItem['id'] ?? 0); ?>">
                <div class="past-admin-form-grid">
                    <div class="form-group"><label for="pastTitle">Conference name <b>*</b></label><input id="pastTitle" name="title" class="form-control" value="<?php echo e($editItem['title'] ?? ''); ?>" placeholder="e.g. SCS Annual Conference 2025" required></div>
                    <div class="form-group"><label for="pastDate">Conference date</label><input id="pastDate" type="date" name="conference_date" class="form-control" value="<?php echo !empty($editItem['conference_date']) ? e(date('Y-m-d', strtotime($editItem['conference_date']))) : ''; ?>"></div>
                    <div class="form-group past-admin-full"><label for="pastBody">Conference story <b>*</b></label><textarea id="pastBody" name="body" class="form-control" rows="7" placeholder="Write a short paragraph about the conference, its focus, and key moments..." required><?php echo e($editItem['body'] ?? ''); ?></textarea><small>A simple paragraph is enough. This appears on the archive detail page.</small></div>
                    <div class="form-group past-admin-full">
                        <div class="past-admin-upload-heading"><div><label for="pastGallery">Conference photos</label><small>Add or replace photos from the gathering. JPG, PNG, WEBP, or GIF; up to 5 MB each.</small></div><strong><span id="pastImageCount"><?php echo count($editImages); ?></span> / 10</strong></div>
                        <input id="pastGallery" type="file" name="gallery_images[]" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-existing-count="<?php echo count($editImages); ?>">
                        <div class="past-admin-previews" id="pastImagePreviews" aria-live="polite"></div>
                    </div>
                    <?php if ($editImages): ?>
                        <div class="form-group past-admin-full"><label>Current gallery</label><div class="past-admin-gallery-grid">
                            <?php foreach ($editImages as $image): ?>
                                <label class="past-admin-gallery-item"><img src="<?php echo UPLOADS_URL . '/' . e($image['image_path']); ?>" alt="Conference gallery photo" loading="lazy"><span><input type="checkbox" name="remove_images[]" value="<?php echo (int)$image['id']; ?>"><span>Remove photo</span></span></label>
                            <?php endforeach; ?>
                        </div><small>Select photos to remove, then save your changes.</small></div>
                    <?php endif; ?>
                    <div class="form-group past-admin-full"><label class="past-admin-visibility"><input type="checkbox" name="is_active" value="1" <?php echo !$editItem || !empty($editItem['is_active']) ? 'checked' : ''; ?>><span><strong>Show this conference on the website</strong><small>Turn this off to keep the entry as a draft.</small></span></label></div>
                </div>
                <div class="past-admin-form-actions"><a class="btn-admin btn-admin-secondary" href="<?php echo SITE_URL; ?>/admin/conference-past">Cancel</a><button class="btn-admin btn-admin-primary" type="submit"><i class="ph ph-floppy-disk" aria-hidden="true"></i> Save conference</button></div>
            </form>
        </div>
    </section>
<?php else: ?>
    <section class="admin-card past-admin-list-card">
        <div class="card-header"><div><h2>Archive entries</h2><p>Manage conference descriptions, visibility, and gallery photos.</p></div></div>
        <div class="card-body">
            <?php if ($items): ?>
                <div class="past-admin-list">
                    <?php foreach ($items as $item): ?>
                        <article class="past-admin-row">
                            <div class="past-admin-row-image">
                                <?php if ($item['image_count']): ?>
                                    <?php $thumbStmt = $db->prepare('SELECT image_path FROM past_conference_gallery WHERE past_conference_id = :id ORDER BY display_order ASC, id ASC LIMIT 1'); $thumbStmt->execute([':id' => $item['id']]); $thumb = $thumbStmt->fetchColumn(); ?>
                                    <img src="<?php echo UPLOADS_URL . '/' . e($thumb); ?>" alt="" loading="lazy">
                                <?php else: ?><i class="ph ph-image" aria-hidden="true"></i><?php endif; ?>
                            </div>
                            <div class="past-admin-row-copy"><div class="past-admin-row-meta"><span class="past-admin-status <?php echo $item['is_active'] ? 'is-visible' : 'is-draft'; ?>"><?php echo $item['is_active'] ? 'Visible' : 'Draft'; ?></span><span><?php echo (int)$item['image_count']; ?> / 10 photos</span><?php if ($item['conference_date']): ?><span><?php echo e(date('M j, Y', strtotime($item['conference_date']))); ?></span><?php endif; ?></div><h3><?php echo e($item['title']); ?></h3><p><?php echo e(mb_strimwidth(trim(strip_tags($item['body'] ?? '')), 0, 145, '…')); ?></p></div>
                            <div class="past-admin-row-actions"><a class="btn-admin btn-admin-secondary btn-sm" href="<?php echo SITE_URL; ?>/admin/conference-past?edit=<?php echo (int)$item['id']; ?>"><i class="ph ph-pencil-simple" aria-hidden="true"></i> Edit</a><form method="POST" onsubmit="return confirm('Delete this archive entry and all of its photos?');"><?php echo csrfField(); ?><input type="hidden" name="mode" value="delete"><input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>"><button class="btn-admin btn-admin-danger btn-sm" type="submit"><i class="ph ph-trash" aria-hidden="true"></i><span class="sr-only">Delete <?php echo e($item['title']); ?></span></button></form></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="past-admin-empty"><i class="ph ph-images" aria-hidden="true"></i><h3>Your conference archive starts here</h3><p>Add a past conference, write a short overview, and select up to 10 photos.</p><a href="<?php echo SITE_URL; ?>/admin/conference-past?action=add" class="btn-admin btn-admin-primary"><i class="ph ph-plus" aria-hidden="true"></i> Add first conference</a></div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<script>
(function () {
    var input = document.getElementById('pastGallery');
    var previews = document.getElementById('pastImagePreviews');
    var count = document.getElementById('pastImageCount');
    if (!input || !previews || !count) return;
    var existing = Number(input.dataset.existingCount || 0);
    input.addEventListener('change', function () {
        previews.textContent = '';
        var selected = Array.prototype.slice.call(input.files || []);
        count.textContent = String(existing + selected.length);
        selected.forEach(function (file) {
            if (!file.type.startsWith('image/')) return;
            var image = document.createElement('img');
            image.alt = file.name;
            image.src = URL.createObjectURL(file);
            previews.appendChild(image);
        });
        if (existing + selected.length > 10) {
            count.classList.add('is-over-limit');
        } else {
            count.classList.remove('is-over-limit');
        }
    });
    document.querySelectorAll('input[name="remove_images[]"]').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            var removed = document.querySelectorAll('input[name="remove_images[]"]:checked').length;
            count.textContent = String(existing - removed + (input.files ? input.files.length : 0));
        });
    });
    document.getElementById('pastConferenceForm').addEventListener('submit', function (event) {
        var removed = document.querySelectorAll('input[name="remove_images[]"]:checked').length;
        var total = existing - removed + (input.files ? input.files.length : 0);
        if (total > 10) {
            event.preventDefault();
            count.classList.add('is-over-limit');
            input.focus();
        }
    });
}());
</script>

<?php endAdminLayout(); ?>