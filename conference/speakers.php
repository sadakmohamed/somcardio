<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$conf = $db->query("SELECT * FROM conferences WHERE status = 'active' ORDER BY id DESC LIMIT 1")->fetch();

if (!$conf) {
    $conf = ['id' => 0, 'title' => 'Somali Cardiac Society Annual Conference', 'year' => date('Y'), 'hero_image' => ''];
}

$sql = "SELECT * FROM conference_speakers WHERE conference_id = :cid AND is_active = 1 ORDER BY display_order ASC, id ASC";
if ($conf['id']) {
    $stmt = $db->prepare($sql);
    $stmt->execute([':cid' => $conf['id']]);
    $speakers = $stmt->fetchAll();
} else {
    $speakers = [];
}

$welcome = [];
$keynote = [];
foreach ($speakers as $speaker) {
    $section = $speaker['section'] ?? 'keynote';
    if (in_array($section, ['welcome', 'welcome_ceremony'], true)) {
        $welcome[] = $speaker;
    } else {
        $keynote[] = $speaker;
    }
}

$pageTitle = 'Speakers';
$pageDescription = 'Meet the speakers and presenters at the Somali Cardiac Society Annual Conference.';

?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/conference.css?v=3.8">
<div class="conf-detail-nav-wrap"><?php include __DIR__ . '/../includes/conference_subnav.php'; ?></div>

<section class="conf-speaker-hero" style="--speaker-hero-image:url('<?php echo !empty($conf['hero_image']) ? UPLOADS_URL . '/' . e($conf['hero_image']) : SITE_URL . '/images/hero2.png'; ?>')">
    <div class="container conf-speaker-hero-inner">
        <div class="conf-speaker-hero-copy">
            <span class="conf-speaker-eyebrow"><i></i> 2026 CONFERENCE FACULTY</span>
            <h1>Expert voices · Shared knowledge<br><em>Better cardiovascular care</em></h1>
            <p>Meet the experts bringing clinical insight, scientific evidence, research, and experience to advance cardiovascular care in Somalia and beyond</p>
            <a class="conf-speaker-hero-link" href="#speaker-ceremony">Meet this year’s speakers <span aria-hidden="true">&#8595;</span></a>
        </div>
        <div class="conf-speaker-hero-index"><strong><?php echo str_pad((string)count($speakers), 2, '0', STR_PAD_LEFT); ?></strong><span>FEATURED<br>VOICES</span></div>
    </div>
    <div class="conf-speaker-hero-rule" aria-hidden="true"></div>
</section>

<?php
$speakerSections = [
    ['id' => 'speaker-ceremony', 'eyebrow' => 'THE OPENING', 'title' => 'Welcome & Opening Ceremony', 'description' => 'Leaders and distinguished guests setting the direction for a shared national conversation on cardiovascular health', 'speakers' => $welcome, 'fallback' => 'Opening ceremony speakers will be announced soon.'],
    ['id' => 'speaker-keynotes', 'eyebrow' => 'THE BIG IDEAS', 'title' => 'Keynote Speakers', 'description' => 'Expert perspectives on the science, systems, and future of cardiovascular care', 'speakers' => $keynote, 'fallback' => 'Keynote speaker announcements will be shared soon.'],
];
foreach ($speakerSections as $sectionIndex => $section):
?>
<section class="conf-speaker-section<?php echo $sectionIndex === 0 ? ' conf-speaker-section-tint' : ''; ?>" id="<?php echo e($section['id']); ?>">
    <div class="container">
        <div class="conf-speaker-section-heading">
            <div><span class="conf-speaker-section-eyebrow"><?php echo e($section['eyebrow']); ?> <i></i></span><h2><?php echo e($section['title']); ?></h2></div>
            <p><?php echo e($section['description']); ?></p>
        </div>
        <?php if (!empty($section['speakers'])): ?>
            <div class="conf-speaker-gallery">
                <?php foreach ($section['speakers'] as $speaker): ?>
                    <?php $speakerName = $speaker['full_name'] ?? 'Conference Speaker'; $speakerPosition = $speaker['position_title'] ?? $speaker['position'] ?? 'Guest Speaker'; ?>
                    <article class="conf-speaker-profile" aria-label="<?php echo e($speakerName . ', ' . $speakerPosition); ?>">
                        <?php if (!empty($speaker['photo'])): ?>
                            <img class="conf-speaker-profile-image" src="<?php echo UPLOADS_URL . '/' . e($speaker['photo']); ?>" alt="Portrait of <?php echo e($speakerName); ?>" loading="lazy">
                        <?php else: ?>
                            <div class="conf-speaker-profile-placeholder" aria-hidden="true"><?php echo e(mb_strtoupper(mb_substr($speakerName, 0, 1))); ?></div>
                        <?php endif; ?>
                        <div class="conf-speaker-profile-details">
                            <span class="conf-speaker-profile-tag"><?php echo e($section['title']); ?> / <?php echo e($conf['year'] ?? date('Y')); ?></span>
                            <h3><?php echo e($speakerName); ?></h3>
                            <p><?php echo e($speakerPosition); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="conf-speaker-empty"><i class="ph ph-microphone-stage" aria-hidden="true"></i><p><?php echo e($section['fallback']); ?></p></div>
        <?php endif; ?>
    </div>
</section>
<?php endforeach; ?>

<section class="conf-speaker-cta">
    <div class="container conf-speaker-cta-inner">
        <div>
            <span>BE PART OF THE CONFERENCE</span>
            <h2>Join the conversation shaping the future of cardiovascular care</h2>
        </div>
        <a href="<?php echo SITE_URL; ?>/conference/registration" class="conf-speaker-cta-button">Register now <span aria-hidden="true">&#8594;</span></a>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
