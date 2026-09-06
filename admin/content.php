<?php
/**
 * Content Management — Somali Cardiac Society
 */
require_once __DIR__ . '/../includes/admin_layout.php';

$db = getDB();
try {
    $db->exec("ALTER TABLE content MODIFY COLUMN category VARCHAR(50) NOT NULL");
} catch (Exception $e) {
    // Column already VARCHAR or migration not required
}

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$error = null;
$success = null;

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        setFlash('error', 'Invalid security token.');
        header('Location: ' . SITE_URL . '/admin/content');
        exit;
    }

    if ($action === 'add' || $action === 'edit') {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $category = trim($_POST['category'] ?? 'research');
        $summary = trim($_POST['summary'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $eventDate = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
        $author = trim($_POST['author'] ?? '');
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if (empty($title)) {
            $error = 'Title is required.';
        } else {
            // Auto generate slug if empty
            if (empty($slug)) {
                $slug = generateSlug($title);
            } else {
                $slug = generateSlug($slug);
            }

            // Verify unique slug
            $slugCheck = $db->prepare("SELECT COUNT(*) FROM content WHERE slug = :slug AND id != :id");
            $slugCheck->execute([':slug' => $slug, ':id' => $id]);
            if ($slugCheck->fetchColumn() > 0) {
                $slug .= '-' . rand(100, 999);
            }

            // Feature Image upload
            $imagePath = $_POST['existing_image'] ?? null;
            if (isset($_FILES['feature_image']) && $_FILES['feature_image']['error'] === UPLOAD_ERR_OK) {
                if ($action === 'edit' && $imagePath) {
                    deleteUploadedFile($imagePath);
                }
                $uploaded = handleImageUpload($_FILES['feature_image'], 'content');
                if ($uploaded) {
                    $imagePath = $uploaded;
                } else {
                    $error = 'Failed to upload image. Only JPG, PNG, WEBP allowed (max 5MB).';
                }
            }

            if (!$error) {
                try {
                    if ($action === 'add') {
                        $stmt = $db->prepare("INSERT INTO content (title, slug, category, summary, body, feature_image, event_date, author, is_published) VALUES (:title, :slug, :category, :summary, :body, :feature_image, :event_date, :author, :is_published)");
                        $stmt->execute([
                            ':title'         => $title,
                            ':slug'          => $slug,
                            ':category'      => $category,
                            ':summary'       => $summary,
                            ':body'          => $body,
                            ':feature_image' => $imagePath,
                            ':event_date'    => $eventDate,
                            ':author'        => $author,
                            ':is_published'  => $isPublished
                        ]);
                        setFlash('success', 'Content added successfully.');
                    } else {
                        $stmt = $db->prepare("UPDATE content SET title = :title, slug = :slug, category = :category, summary = :summary, body = :body, feature_image = :feature_image, event_date = :event_date, author = :author, is_published = :is_published WHERE id = :id");
                        $stmt->execute([
                            ':title'         => $title,
                            ':slug'          => $slug,
                            ':category'      => $category,
                            ':summary'       => $summary,
                            ':body'          => $body,
                            ':feature_image' => $imagePath,
                            ':event_date'    => $eventDate,
                            ':author'        => $author,
                            ':is_published'  => $isPublished,
                            ':id'            => $id
                        ]);
                        setFlash('success', 'Content updated successfully.');
                    }
                    header('Location: ' . SITE_URL . '/admin/content');
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
        $stmt = $db->prepare("SELECT feature_image FROM content WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $image = $stmt->fetchColumn();
        if ($image) deleteUploadedFile($image);

        $stmt = $db->prepare("DELETE FROM content WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Content deleted successfully.');
    } catch (Exception $ex) {
        setFlash('error', 'Failed to delete content.');
    }
    header('Location: ' . SITE_URL . '/admin/content');
    exit;
}

// Display Views
if ($action === 'add' || $action === 'edit') {
    $content = ['title' => '', 'slug' => '', 'category' => 'research', 'summary' => '', 'body' => '', 'feature_image' => '', 'event_date' => '', 'author' => '', 'is_published' => 1];
    if ($action === 'edit' && $id > 0) {
        $stmt = $db->prepare("SELECT * FROM content WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $content = $stmt->fetch();
        if (!$content) {
            setFlash('error', 'Content not found.');
            header('Location: ' . SITE_URL . '/admin/content');
            exit;
        }
    }

    startAdminLayout(($action === 'add' ? 'Add New' : 'Edit') . ' Content');
    ?>
    <div class="page-title-block">
        <div>
            <h1><?php echo $action === 'add' ? 'Create Content Item' : 'Edit Content Details'; ?></h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Publish research, updates, news, or upcoming events.</p>
        </div>
        <a href="<?php echo SITE_URL; ?>/admin/content" class="btn-admin btn-admin-secondary">Back to List</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="card-body">
            <form action="<?php echo SITE_URL; ?>/admin/content?action=<?php echo $action; ?>&id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="admin-form">
                <?php echo csrfField(); ?>
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($content['feature_image'] ?? ''); ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="contentTitle">Title *</label>
                        <input type="text" id="contentTitle" name="title" required value="<?php echo htmlspecialchars($content['title']); ?>" placeholder="Enter content title">
                    </div>

                    <div class="form-group">
                        <label for="contentSlug">Slug (URL string)</label>
                        <input type="text" id="contentSlug" name="slug" value="<?php echo htmlspecialchars($content['slug']); ?>" placeholder="auto-generated-if-blank">
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="guidelines" <?php echo $content['category'] === 'guidelines' ? 'selected' : ''; ?>>Clinical Guidelines</option>
                            <option value="research" <?php echo $content['category'] === 'research' ? 'selected' : ''; ?>>Research</option>
                            <option value="publication" <?php echo $content['category'] === 'publication' ? 'selected' : ''; ?>>Publication</option>
                            <option value="education" <?php echo $content['category'] === 'education' ? 'selected' : ''; ?>>Education</option>
                            <option value="seminar" <?php echo $content['category'] === 'seminar' ? 'selected' : ''; ?>>Seminar</option>
                            <option value="webinar" <?php echo $content['category'] === 'webinar' ? 'selected' : ''; ?>>Webinar</option>
                            <option value="workshop" <?php echo $content['category'] === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                            <option value="course" <?php echo $content['category'] === 'course' ? 'selected' : ''; ?>>Course</option>
                            <option value="news" <?php echo $content['category'] === 'news' ? 'selected' : ''; ?>>News</option>
                            <option value="events" <?php echo $content['category'] === 'events' ? 'selected' : ''; ?>>Events</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="author">Author / Publisher</label>
                        <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($content['author'] ?? ''); ?>" placeholder="e.g. Dr. Ahmed Hassan">
                    </div>

                    <div class="form-group">
                        <label for="event_date">Event Date (Events/Workshops/Conferences)</label>
                        <input type="date" id="event_date" name="event_date" value="<?php echo htmlspecialchars($content['event_date'] ?? ''); ?>">
                    </div>

                    <div class="form-group" style="display: flex; align-items: flex-end; padding-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                            <input type="checkbox" name="is_published" value="1" <?php echo $content['is_published'] ? 'checked' : ''; ?>>
                            Published (Visible on site)
                        </label>
                    </div>

                    <div class="form-group full-width">
                        <label for="feature_image">Featured Image</label>
                        <div class="file-upload-preview">
                            <div class="image-preview-box" style="width: 140px; height: 85px;">
                                <img id="imagePreview" src="<?php echo $content['feature_image'] ? UPLOADS_URL . '/' . htmlspecialchars($content['feature_image']) : ''; ?>" alt="Preview" style="<?php echo $content['feature_image'] ? '' : 'display:none;'; ?>">
                                <?php if (!$content['feature_image']): ?>
                                    <span style="font-size: 1.5rem; color: var(--text-light);">🖼️</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <input type="file" id="feature_image" name="feature_image" class="image-upload-input" data-preview="imagePreview" data-crop-ratio="1.77777777778" accept="image/*">
                                <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">Landscape crop (16:9). Max 5MB. Use the crop tool to select the best portion.</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Excerpt / Summary *</label>
                        <!-- Quill editor visible container -->
                        <div id="summaryEditor" data-placeholder="Provide a brief summary for grid and card views..." style="min-height:90px;background:#fff;"></div>
                        <!-- Hidden textarea that gets submitted -->
                        <textarea id="summary" name="summary" style="display:none;"><?php echo htmlspecialchars($content['summary'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label>Content Body *</label>
                        <!-- Quill editor visible container -->
                        <div id="bodyEditor" data-placeholder="Write the full content, article or details here..." style="min-height:260px;background:#fff;"></div>
                        <!-- Hidden textarea that gets submitted -->
                        <textarea id="body" name="body" style="display:none;"><?php echo htmlspecialchars($content['body'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="<?php echo SITE_URL; ?>/admin/content" class="btn-admin btn-admin-secondary">Cancel</a>
                    <button type="submit" class="btn-admin btn-admin-primary">Save Content</button>
                </div>
            </form>
        </div>
    </div>
    <?php
    endAdminLayout();
} else {
    // List View
    try {
        $stmt = $db->query("SELECT * FROM content ORDER BY COALESCE(event_date, created_at) DESC");
        $contentList = $stmt->fetchAll();
    } catch (Exception $ex) {
        $contentList = [];
    }

    startAdminLayout('Manage Content');
    ?>
    <div class="page-title-block">
        <div>
            <h1>Manage Website Content</h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Add, edit, or delete published papers, guidelines, news, and events.</p>
        </div>
        <a href="<?php echo SITE_URL; ?>/admin/content?action=add" class="btn-admin btn-admin-primary">Create Content Item</a>
    </div>

    <!-- Search Card -->
    <div class="admin-card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 15px 24px;">
            <input type="text" id="tableSearch" placeholder="Search content by title, category, author..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 0.9rem;">
        </div>
    </div>

    <div class="admin-card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="admin-table" id="dataTable">
                    <thead>
                        <tr>
                            <th>Content Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Event Date</th>
                            <th>Published</th>
                            <th>Date</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($contentList)): ?>
                            <?php foreach ($contentList as $item): ?>
                            <tr>
                                <td>
                                    <strong style="display: block; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($item['title']); ?></strong>
                                    <span style="font-size: 0.75rem; color: var(--text-secondary);"><?php echo htmlspecialchars($item['slug']); ?></span>
                                </td>
                                <td>
                                    <span class="status-badge" style="background: rgba(0, 168, 223, 0.08); color: var(--primary-blue);">
                                        <?php echo ucfirst($item['category']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($item['author'] ?: 'SCS Admin'); ?></td>
                                <td><?php echo $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : '<span style="color:var(--text-light);">-</span>'; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $item['is_published'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $item['is_published'] ? 'Published' : 'Draft'; ?>
                                    </span>
                                </td>
                                <td><?php echo $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : date('M d, Y', strtotime($item['created_at'])); ?></td>
                                <td style="text-align: right;">
                                    <a href="<?php echo SITE_URL; ?>/admin/content?action=edit&id=<?php echo $item['id']; ?>" class="btn-icon" title="Edit Content">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/admin/content?action=delete&id=<?php echo $item['id']; ?>" class="btn-icon btn-icon-danger confirm-delete" data-item="content item" title="Delete Content">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-light); padding: 50px;">No content items found.</td>
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
