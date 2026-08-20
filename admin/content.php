<?php
/**
 * Content Management — Somali Cardiac Society
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
        header('Location: content.php');
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
                // Delete existing photo if editing
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
                            ':title' => $title,
                            ':slug' => $slug,
                            ':category' => $category,
                            ':summary' => $summary,
                            ':body' => $body,
                            ':feature_image' => $imagePath,
                            ':event_date' => $eventDate,
                            ':author' => $author,
                            ':is_published' => $isPublished
                        ]);
                        setFlash('success', 'Content added successfully.');
                    } else {
                        $stmt = $db->prepare("UPDATE content SET title = :title, slug = :slug, category = :category, summary = :summary, body = :body, feature_image = :feature_image, event_date = :event_date, author = :author, is_published = :is_published WHERE id = :id");
                        $stmt->execute([
                            ':title' => $title,
                            ':slug' => $slug,
                            ':category' => $category,
                            ':summary' => $summary,
                            ':body' => $body,
                            ':feature_image' => $imagePath,
                            ':event_date' => $eventDate,
                            ':author' => $author,
                            ':is_published' => $isPublished,
                            ':id' => $id
                        ]);
                        setFlash('success', 'Content updated successfully.');
                    }
                    header('Location: content.php');
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
        $stmt = $db->prepare("SELECT feature_image FROM content WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $image = $stmt->fetchColumn();
        if ($image) {
            deleteUploadedFile($image);
        }

        $stmt = $db->prepare("DELETE FROM content WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Content deleted successfully.');
    } catch (Exception $ex) {
        setFlash('error', 'Failed to delete content.');
    }
    header('Location: content.php');
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
            header('Location: content.php');
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
        <a href="content.php" class="btn-admin btn-admin-secondary">Back to List</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="card-body">
            <form action="content.php?action=<?php echo $action; ?>&id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="admin-form">
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
                            <option value="research" <?php echo $content['category'] === 'research' ? 'selected' : ''; ?>>Research</option>
                            <option value="education" <?php echo $content['category'] === 'education' ? 'selected' : ''; ?>>Education</option>
                            <option value="news" <?php echo $content['category'] === 'news' ? 'selected' : ''; ?>>News</option>
                            <option value="events" <?php echo $content['category'] === 'events' ? 'selected' : ''; ?>>Events</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="author">Author / Publisher</label>
                        <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($content['author'] ?? ''); ?>" placeholder="e.g. Dr. Ahmed Hassan">
                    </div>

                    <div class="form-group">
                        <label for="event_date">Event Date (Events only)</label>
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
                            <div class="image-preview-box" style="width: 120px; height: 80px;">
                                <img id="imagePreview" src="<?php echo $content['feature_image'] ? UPLOADS_URL . '/' . htmlspecialchars($content['feature_image']) : ''; ?>" alt="Preview" style="<?php echo $content['feature_image'] ? '' : 'display:none;'; ?>">
                                <?php if (!$content['feature_image']): ?>
                                    <span style="font-size: 1.5rem; color: var(--text-light);">🖼️</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <input type="file" id="feature_image" name="feature_image" class="image-upload-input" data-preview="imagePreview" accept="image/*">
                                <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">Recommended: 16:9 ratio. Max file size: 5MB.</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="summary">Excerpt / Summary *</label>
                        <textarea id="summary" name="summary" rows="3" required placeholder="Provide a brief summary for grid views..."><?php echo htmlspecialchars($content['summary'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="body">Content Body *</label>
                        <textarea id="body" name="body" rows="12" required placeholder="Write the main content/article here..."><?php echo htmlspecialchars($content['body'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="content.php" class="btn-admin btn-admin-secondary">Cancel</a>
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
        $stmt = $db->query("SELECT * FROM content ORDER BY created_at DESC");
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
        <a href="content.php?action=add" class="btn-admin btn-admin-primary">Create Content Item</a>
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
                            <th>Created At</th>
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
                                <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                <td style="text-align: right;">
                                    <a href="content.php?action=edit&id=<?php echo $item['id']; ?>" class="btn-icon" title="Edit Content">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    <a href="content.php?action=delete&id=<?php echo $item['id']; ?>" class="btn-icon btn-icon-danger confirm-delete" data-item="content item" title="Delete Content">
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
