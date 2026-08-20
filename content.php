<?php
/**
 * Content Page — Research & Education / News & Events
 */
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'Research & News';
$pageDescription = 'Explore the latest research, educational resources, news, and events from the Somali Cardiac Society.';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;

try {
    $db = getDB();

    // Single content detail view
    if ($slug) {
        $stmt = $db->prepare("SELECT * FROM content WHERE slug = :slug AND is_published = 1 LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $article = $stmt->fetch();
        if (!$article) {
            header('Location: content.php');
            exit;
        }
        $pageTitle = $article['title'];
    } else {
        $stmt = $db->query("SELECT * FROM content WHERE is_published = 1 ORDER BY created_at DESC");
        $allContent = $stmt->fetchAll();
    }
} catch (Exception $ex) {
    $allContent = [];
    $article = null;
}

include __DIR__ . '/includes/header.php';
?>

<?php if (isset($article) && $article): ?>
<!-- Single Content Detail -->
<div class="page-header">
    <div class="container">
        <h1 style="font-size:1.8rem;max-width:700px;margin:0 auto;"><?php echo e($article['title']); ?></h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <a href="content.php">Content</a>
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
                <?php if ($article['event_date']): ?>
                <span style="color:var(--primary-red);font-weight:600;">
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
                    Event: <?php echo date('M d, Y', strtotime($article['event_date'])); ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="body">
                <?php echo nl2br(e($article['body'])); ?>
            </div>
            <div style="margin-top:40px;padding-top:24px;border-top:1px solid var(--border-color);">
                <a href="content.php" class="btn btn-outline">← Back to All Content</a>
            </div>
        </div>
    </div>
</section>

<?php else: ?>
<!-- Content Listing -->
<div class="page-header">
    <div class="container">
        <h1>Research & Education · News & Events</h1>
        <p>Explore our latest publications, clinical guidelines, society updates, and events</p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <span style="color:rgba(255,255,255,0.8);">Content</span>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Filter Tabs -->
        <div class="filter-tabs fade-in">
            <button class="filter-tab active" data-filter="all">All</button>
            <button class="filter-tab" data-filter="research">Research</button>
            <button class="filter-tab" data-filter="education">Education</button>
            <button class="filter-tab" data-filter="news">News</button>
            <button class="filter-tab" data-filter="events">Events</button>
        </div>

        <?php if (!empty($allContent)): ?>
        <div class="content-grid" style="grid-template-columns: repeat(2, 1fr);">
            <?php foreach ($allContent as $item): ?>
            <div class="card fade-in" data-category="<?php echo e($item['category']); ?>" style="transition: opacity 0.3s, transform 0.3s;">
                <?php if ($item['feature_image']): ?>
                    <img src="<?php echo UPLOADS_URL . '/' . e($item['feature_image']); ?>" alt="<?php echo e($item['title']); ?>" class="card-image">
                <?php else: ?>
                    <div class="card-image" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--primary-blue-light),var(--bg-gray));">
                        <?php
                        $icons = [
                            'research' => '<svg width="36" height="36" fill="var(--primary-blue)" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>',
                            'education' => '<svg width="36" height="36" fill="var(--primary-blue)" viewBox="0 0 24 24"><path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg>',
                            'news' => '<svg width="36" height="36" fill="var(--primary-red)" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>',
                            'events' => '<svg width="36" height="36" fill="var(--primary-red)" viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>'
                        ];
                        echo $icons[$item['category']] ?? $icons['research'];
                        ?>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <span class="card-badge badge-<?php echo e($item['category']); ?>"><?php echo e(ucfirst($item['category'])); ?></span>
                    <h3 class="card-title"><?php echo e($item['title']); ?></h3>
                    <p class="card-text"><?php echo e(substr($item['summary'] ?? '', 0, 140)); ?>...</p>
                    <div class="card-meta">
                        <span>
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                            <?php echo date('M d, Y', strtotime($article['created_at'] ?? $item['created_at'])); ?>
                        </span>
                        <?php if ($item['event_date']): ?>
                        <span style="color:var(--primary-red);font-weight:600;">Event: <?php echo date('M d', strtotime($item['event_date'])); ?></span>
                        <?php endif; ?>
                        <a href="content.php?slug=<?php echo e($item['slug']); ?>" style="color:var(--primary-blue);font-weight:600;margin-left:auto;">Read More →</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:60px 20px;">
            <svg width="60" height="60" fill="var(--primary-blue)" viewBox="0 0 24 24" style="margin-bottom:20px;"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            <h3 style="margin-bottom:8px;">Content Coming Soon</h3>
            <p style="color:var(--text-light);">Our research papers, news, and events will be published here shortly.</p>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
