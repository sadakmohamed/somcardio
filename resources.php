<?php
/**
 * Resources Page — Somali Cardiac Society
 */
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'Resources';
$pageDescription = 'Access clinical guidelines, research, publications, education, and training from the Somali Cardiac Society.';

$category = isset($_GET['category']) ? trim($_GET['category']) : null;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;

try {
    $db = getDB();

    if ($slug) {
        $stmt = $db->prepare("SELECT * FROM content WHERE slug = :slug AND is_published = 1 LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $article = $stmt->fetch();
        if (!$article) {
            header('Location: resources.php');
            exit;
        }
        $pageTitle = $article['title'];
    } else {
        // Exclude news & events from Resources page
        $stmt = $db->query("SELECT * FROM content WHERE is_published = 1 AND category NOT IN ('news', 'events') ORDER BY created_at DESC");
        $allContent = $stmt->fetchAll();
    }
} catch (Exception $ex) {
    $allContent = [];
    $article = null;
}

include __DIR__ . '/includes/header.php';
?>

<?php if (isset($article) && $article): ?>
<!-- Single Resource Detail -->
<div class="page-header">
    <div class="container">
        <h1 style="font-size:1.8rem;max-width:700px;margin:0 auto;"><?php echo e($article['title']); ?></h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <a href="resources.php">Resources</a>
            <span>›</span>
            <span style="color:rgba(255,255,255,0.8);"><?php echo e(ucfirst($article['category'])); ?></span>
        </div>
    </div>
</div>
<section class="section">
    <div class="container">
        <div class="content-detail fade-in">
            <?php if ($article['feature_image']): ?>
                <img src="<?php echo UPLOADS_URL . '/' . e($article['feature_image']); ?>" alt="<?php echo e($article['title']); ?>" class="feature-img">
            <?php endif; ?>
            <div class="meta">
                <span class="card-badge badge-<?php echo e($article['category']); ?>"><?php echo e(ucfirst($article['category'])); ?></span>
                <span>
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                    <?php echo date('F d, Y', strtotime($article['created_at'])); ?>
                </span>
                <?php if ($article['author']): ?>
                <span>
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    <?php echo e($article['author']); ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="body">
                <?php echo nl2br(e($article['body'])); ?>
            </div>
            <div style="margin-top:40px;padding-top:24px;border-top:1px solid var(--border-color);">
                <a href="resources.php" class="btn btn-outline">← Back to Resources</a>
            </div>
        </div>
    </div>
</section>

<?php else: ?>
<!-- Resources Listing -->
<div class="page-header" style="background: linear-gradient(135deg, rgba(0,27,46,0.9), rgba(0,40,69,0.68)), url('<?php echo SITE_URL; ?>/images/resource.png') center/cover no-repeat;">
    <div class="container">
        <h1>Resources</h1>
        <p>Explore clinical guidelines, research & publications, and education & training materials</p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <span style="color:rgba(255,255,255,0.8);">Resources</span>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Filter Tabs for Resources -->
        <div class="filter-tabs fade-in">
            <button class="filter-tab <?php echo (!$category || $category === 'all') ? 'active' : ''; ?>" data-filter="all">All</button>
            <button class="filter-tab <?php echo ($category === 'guidelines') ? 'active' : ''; ?>" data-filter="guidelines">Clinical Guidelines</button>
            <button class="filter-tab <?php echo ($category === 'research') ? 'active' : ''; ?>" data-filter="research">Research &amp; Publications</button>
            <button class="filter-tab <?php echo ($category === 'education') ? 'active' : ''; ?>" data-filter="education">Education &amp; Training</button>
        </div>

        <?php if (!empty($allContent)): ?>
        <div class="content-grid" style="grid-template-columns: repeat(2, 1fr);">
            <?php foreach ($allContent as $item): ?>
            <div class="card fade-in" data-category="<?php echo e($item['category']); ?>" style="transition: opacity 0.3s, transform 0.3s;">
                <?php if ($item['feature_image']): ?>
                    <img src="<?php echo UPLOADS_URL . '/' . e($item['feature_image']); ?>" alt="<?php echo e($item['title']); ?>" class="card-image">
                <?php else: ?>
                    <div class="card-image" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--primary-blue-light),var(--bg-gray));">
                        <svg width="36" height="36" fill="var(--primary-blue)" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <span class="card-badge badge-<?php echo e($item['category']); ?>"><?php echo e(ucfirst($item['category'])); ?></span>
                    <h3 class="card-title"><?php echo e($item['title']); ?></h3>
                    <p class="card-text"><?php echo e(substr($item['summary'] ?? '', 0, 140)); ?>...</p>
                    <div class="card-meta">
                        <span>
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                            <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                        </span>
                        <a href="resources.php?slug=<?php echo e($item['slug']); ?>" style="color:var(--primary-blue);font-weight:600;margin-left:auto;">Read More →</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:60px 20px;">
            <svg width="60" height="60" fill="var(--primary-blue)" viewBox="0 0 24 24" style="margin-bottom:20px;"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            <h3 style="margin-bottom:8px;">Resources Coming Soon</h3>
            <p style="color:var(--text-light);">Our clinical guidelines, research papers, and educational resources will be published here shortly.</p>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
