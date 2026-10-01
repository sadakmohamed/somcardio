<?php
require_once __DIR__ . '/../config/auth.php';

$db = getDB();
$slug = trim($_GET['slug'] ?? '');
$conferenceStmt = $db->prepare('SELECT * FROM past_conferences WHERE slug = :slug AND is_active = 1 LIMIT 1');
$conferenceStmt->execute([':slug' => $slug]);
$conference = $conferenceStmt->fetch();

if (!$conference) {
    header('Location: ' . SITE_URL . '/conference/past', true, 302);
    exit;
}

$galleryStmt = $db->prepare('SELECT * FROM past_conference_gallery WHERE past_conference_id = :id ORDER BY display_order ASC, id ASC LIMIT 10');
$galleryStmt->execute([':id' => $conference['id']]);
$gallery = $galleryStmt->fetchAll();
$dateLabel = !empty($conference['conference_date']) ? date('F j, Y', strtotime($conference['conference_date'])) : 'SCS conference archive';

$pageTitle = $conference['title'];
$pageDescription = mb_strimwidth(trim(strip_tags($conference['body'] ?? '')), 0, 155, '…');
require_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/conference.css?v=3.8">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<div class="conf-detail-nav-wrap"><?php include __DIR__ . '/../includes/conference_subnav.php'; ?></div>

<section class="past-detail-hero">
    <?php if ($gallery): ?><img class="past-detail-hero-image" src="<?php echo UPLOADS_URL . '/' . e($gallery[0]['image_path']); ?>" alt="<?php echo e($conference['title']); ?>" fetchpriority="high"><?php endif; ?>
    <div class="past-detail-hero-shade"></div>
    <div class="container past-detail-hero-content">
        <a class="past-detail-back" href="<?php echo SITE_URL; ?>/conference/past"><i class="ph ph-arrow-left" aria-hidden="true"></i> Conference archive</a>
        <span class="past-detail-date"><i class="ph ph-calendar-blank" aria-hidden="true"></i> <?php echo e($dateLabel); ?></span>
        <h1><?php echo e($conference['title']); ?></h1>
        <p><?php echo count($gallery); ?> photographs <span aria-hidden="true">·</span> Somali Cardiac Society</p>
    </div>
</section>

<main class="past-detail-page">
    <section class="past-detail-story-section">
        <div class="container past-detail-story-layout">
            <div class="past-detail-story-label"><span>THE STORY</span><i></i><span><?php echo e($dateLabel); ?></span></div>
            <article class="past-detail-story">
                <span class="past-detail-kicker">FROM THE SCS ARCHIVE</span>
                <h2>A gathering remembered.</h2>
                <div class="past-detail-body">
                    <?php if (trim((string)$conference['body']) !== ''): ?>
                        <?php foreach (preg_split('/\R\s*\R/', trim($conference['body'])) as $paragraph): ?>
                            <?php if (trim($paragraph) !== ''): ?><p><?php echo nl2br(e(trim($paragraph))); ?></p><?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Conference highlights and the story of this gathering will be added soon.</p>
                    <?php endif; ?>
                </div>
            </article>
        </div>
    </section>

    <?php if ($gallery): ?>
        <section class="past-detail-gallery-section">
            <div class="container">
                <div class="past-detail-gallery-heading"><div><span class="past-detail-kicker">MOMENTS FROM THE MEETING</span><h2>Conference gallery</h2></div><span><?php echo count($gallery); ?> / 10 PHOTOS</span></div>
                <div class="past-detail-gallery-grid">
                    <?php foreach ($gallery as $index => $image): ?>
                        <button type="button" class="past-detail-photo" data-photo-src="<?php echo UPLOADS_URL . '/' . e($image['image_path']); ?>" data-photo-alt="<?php echo e($image['caption'] ?: $conference['title'] . ' photo ' . ($index + 1)); ?>" aria-label="Open photo <?php echo $index + 1; ?>">
                            <img src="<?php echo UPLOADS_URL . '/' . e($image['image_path']); ?>" alt="<?php echo e($image['caption'] ?: $conference['title'] . ' photo ' . ($index + 1)); ?>" loading="lazy">
                            <span><i class="ph ph-arrows-out-simple" aria-hidden="true"></i></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="past-detail-footer-cta"><div class="container"><span>CONTINUE EXPLORING</span><h2>More stories from the SCS archive.</h2><a href="<?php echo SITE_URL; ?>/conference/past">All past conferences <i class="ph ph-arrow-right" aria-hidden="true"></i></a></div></section>
</main>

<dialog class="past-photo-dialog" id="pastPhotoDialog" aria-label="Conference photo viewer"><button type="button" class="past-photo-close" aria-label="Close photo viewer"><i class="ph ph-x" aria-hidden="true"></i></button><img src="" alt=""><p></p></dialog>
<script>
(function () {
    var dialog = document.getElementById('pastPhotoDialog');
    if (!dialog) return;
    var image = dialog.querySelector('img');
    var caption = dialog.querySelector('p');
    document.querySelectorAll('.past-detail-photo').forEach(function (button) {
        button.addEventListener('click', function () {
            image.src = button.dataset.photoSrc;
            image.alt = button.dataset.photoAlt;
            caption.textContent = button.dataset.photoAlt;
            dialog.showModal();
        });
    });
    dialog.querySelector('.past-photo-close').addEventListener('click', function () { dialog.close(); });
    dialog.addEventListener('click', function (event) { if (event.target === dialog) dialog.close(); });
}());
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
