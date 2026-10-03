<?php
require_once __DIR__ . '/../config/auth.php';

$db = getDB();
$pastConferences = $db->query(
    "SELECT p.*,
            (SELECT g.image_path FROM past_conference_gallery g WHERE g.past_conference_id = p.id ORDER BY g.display_order, g.id LIMIT 1) AS cover_image,
            (SELECT COUNT(*) FROM past_conference_gallery gc WHERE gc.past_conference_id = p.id) AS image_count
     FROM past_conferences p
     WHERE p.is_active = 1
     ORDER BY p.conference_date DESC, p.id DESC"
)->fetchAll();

$pageTitle = 'Past Conferences';
$pageDescription = 'Explore previous Somali Cardiac Society conferences, stories, and photo galleries.';
require_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/conference.css?v=3.8">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<div class="conf-detail-nav-wrap"><?php include __DIR__ . '/../includes/conference_subnav.php'; ?></div>

<section class="past-public-hero">
    <div class="container past-public-hero-inner">
        <div class="past-public-hero-copy"><span><i></i> FROM THE SCS ARCHIVE</span><h1>A milestone for<br><em>cardiovascular care.</em></h1><p>Stories, shared discoveries, and moments from Somali Cardiac Society gatherings.</p></div>
        <div class="past-public-hero-count"><strong><?php echo str_pad((string)count($pastConferences), 2, '0', STR_PAD_LEFT); ?></strong><span>ARCHIVED<br>GATHERINGS</span></div>
    </div>
</section>

<main class="past-public-page">
    <section class="past-public-listing">
        <div class="container">
            <div class="past-public-heading"><div><span>EXPLORE THE ARCHIVE</span><h2>Past conferences</h2></div><p>Browse each gathering’s story and photo collection.</p></div>
            <?php if ($pastConferences): ?>
                <div class="past-public-grid">
                    <?php foreach ($pastConferences as $conference): ?>
                        <?php
                        $dateLabel = !empty($conference['conference_date']) ? date('F j, Y', strtotime($conference['conference_date'])) : 'Conference archive';
                        $bodyText = trim(strip_tags($conference['body'] ?? ''));
                        $excerpt = $bodyText !== '' ? mb_strimwidth($bodyText, 0, 175, '…') : 'Read the conference story and browse highlights from the event.';
                        ?>
                        <article class="past-public-card">
                            <a class="past-public-cover" href="<?php echo SITE_URL; ?>/conference/past-detail?slug=<?php echo rawurlencode($conference['slug']); ?>" aria-label="View <?php echo e($conference['title']); ?>">
                                <?php if (!empty($conference['cover_image'])): ?>
                                    <img src="<?php echo UPLOADS_URL . '/' . e($conference['cover_image']); ?>" alt="Photos from <?php echo e($conference['title']); ?>" loading="lazy">
                                <?php else: ?>
                                    <span class="past-public-cover-placeholder"><i class="ph ph-heartbeat" aria-hidden="true"></i></span>
                                <?php endif; ?>
                                <span class="past-public-cover-date"><?php echo e($dateLabel); ?></span>
                                <span class="past-public-cover-arrow"><i class="ph ph-arrow-up-right" aria-hidden="true"></i></span>
                            </a>
                            <div class="past-public-card-body">
                                <div class="past-public-card-meta"><span><i class="ph ph-images" aria-hidden="true"></i> <?php echo (int)$conference['image_count']; ?> photos</span><span>ARCHIVE</span></div>
                                <h3><?php echo e($conference['title']); ?></h3>
                                <p><?php echo e($excerpt); ?></p>
                                <a class="past-public-read-link" href="<?php echo SITE_URL; ?>/conference/past-detail?slug=<?php echo rawurlencode($conference['slug']); ?>">View conference story <i class="ph ph-arrow-right" aria-hidden="true"></i></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="past-public-empty"><span><i class="ph ph-archive-box" aria-hidden="true"></i></span><h3>The archive is taking shape</h3><p>Past SCS conferences will appear here as stories and photo galleries are published.</p><a href="<?php echo SITE_URL; ?>/conference">Back to this year’s conference <i class="ph ph-arrow-right" aria-hidden="true"></i></a></div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
