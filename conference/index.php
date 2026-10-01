<?php
/**
 * Conference Landing Page — Somali Cardiac Society
 * Displays the active (ongoing) conference as a featured card with links to sub-pages,
 * plus a grid of past conferences.
 */
require_once __DIR__ . '/../config/auth.php';

$pageTitle       = 'Conference';
$pageDescription = 'Somali Cardiac Society Annual Conference — connecting cardiac professionals, researchers, and healthcare leaders across Somalia and East Africa.';

$db = getDB();

/* ── Active conference ──────────────────────────────────────────────── */
try {
    $stmtActive = $db->prepare(
        "SELECT c.*
         FROM conferences c
         WHERE c.status = 'active'
         ORDER BY c.year DESC, c.id DESC
         LIMIT 1"
    );
    $stmtActive->execute();
    $activeConf = $stmtActive->fetch();
} catch (Exception $ex) {
    $activeConf = null;
}

/* ── Past conferences (status = 'past', active = 1) ────────────────── */
try {
    $stmtPast = $db->prepare(
        "SELECT c.*
         FROM past_conferences c
         WHERE c.is_active = 1
         ORDER BY c.conference_date DESC, c.id DESC"
    );
    $stmtPast->execute();
    $pastConfs = $stmtPast->fetchAll();
} catch (Exception $ex) {
    $pastConfs = [];
}

$conferenceYear = $activeConf['year'] ?? date('Y');
$conferenceTitle = trim((string)($activeConf['title'] ?? '')) ?: 'Somali Cardiac Society Annual Conference';
$conferenceIntro = trim(strip_tags((string)($activeConf['hero_text'] ?? '')));
if ($conferenceIntro === '') {
    $conferenceIntro = 'Connecting people, evidence, and ideas to advance cardiovascular health across Somalia and the region.';
} elseif (mb_strlen($conferenceIntro) > 190) {
    $introExcerpt = mb_substr($conferenceIntro, 0, 187);
    $lastSpace = mb_strrpos($introExcerpt, ' ');
    $conferenceIntro = rtrim(mb_substr($introExcerpt, 0, $lastSpace ?: 187)) . '...';
}
$backgroundText = $activeConf['background_text'] ?? '';
$headName = $activeConf['head_name'] ?? '';
$headMessage = $activeConf['head_message'] ?? '';
$headPhoto = $activeConf['head_photo'] ?? '';

$objectiveSource = $activeConf['objectives'] ?? '';
$objectives = $objectiveSource !== ''
    ? preg_split('/<\/li>|<br\s*\/?>|<\/p>|\r\n|\r|\n/i', $objectiveSource, -1, PREG_SPLIT_NO_EMPTY)
    : [];
$objectives = array_values(array_filter(array_map(static function ($item) {
    return trim(html_entity_decode(strip_tags($item), ENT_QUOTES, 'UTF-8'));
}, $objectives)));
if (!$objectives) {
    $objectives = [
        'Strengthen clinical knowledge and evidence-based cardiovascular practice.',
        'Connect regional professionals, institutions, and research teams.',
        'Build practical partnerships that improve patient outcomes.',
    ];
}

$attendeeSource = $activeConf['who_should_attend'] ?? '';
$attendees = is_string($attendeeSource) ? json_decode($attendeeSource, true) : null;
if (!is_array($attendees)) {
    $attendees = preg_split('/[,\r\n]+/', (string)$attendeeSource, -1, PREG_SPLIT_NO_EMPTY);
}
$attendees = array_values(array_filter(array_map(static function ($item) {
    return trim((string)$item);
}, $attendees ?: [])));
if (!$attendees) {
    $attendees = ['Cardiologists and clinicians', 'Nurses and allied health professionals', 'Researchers and academics', 'Medical students and trainees', 'Health institutions and policymakers', 'Development and community partners'];
}

$attendeeDetails = [
    'cardiologist' => ['stethoscope', 'Clinical practice', 'Share approaches to diagnosis, treatment, and long-term cardiac care.'],
    'clinician' => ['stethoscope', 'Clinical practice', 'Share approaches to diagnosis, treatment, and long-term cardiac care.'],
    'nurse' => ['first-aid-kit', 'Allied health', 'Bring essential care-team perspectives into the clinical conversation.'],
    'research' => ['flask', 'Research & academia', 'Present findings, test ideas, and build new collaborations.'],
    'academic' => ['flask', 'Research & academia', 'Present findings, test ideas, and build new collaborations.'],
    'student' => ['graduation-cap', 'Students & trainees', 'Learn from regional experts and connect with mentors.'],
    'trainee' => ['graduation-cap', 'Students & trainees', 'Learn from regional experts and connect with mentors.'],
    'government' => ['landmark', 'Health leadership', 'Connect evidence with policy and stronger health systems.'],
    'policy' => ['landmark', 'Health leadership', 'Connect evidence with policy and stronger health systems.'],
    'ngo' => ['handshake', 'Partners', 'Coordinate efforts that expand access to cardiovascular services.'],
    'partner' => ['handshake', 'Partners', 'Coordinate efforts that expand access to cardiovascular services.'],
    'civil society' => ['users-three', 'Community & civil society', 'Bring community priorities into cardiovascular health planning.'],
];

require_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/conference.css?v=3.8">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<div class="conf-index-nav-wrap"><?php include __DIR__ . '/../includes/conference_subnav.php'; ?></div>

<section class="conf-index-hero">
    <img class="conf-index-hero-image" src="<?php echo !empty($activeConf['hero_image']) ? UPLOADS_URL . '/' . e($activeConf['hero_image']) : SITE_URL . '/images/hero2.png'; ?>" alt="<?php echo e($conferenceTitle); ?>">
    <div class="conf-index-hero-shade"></div>
    <div class="container conf-index-hero-inner">
        <div class="conf-index-hero-copy">
            <span class="conf-index-eyebrow"><span></span> SOMALI CARDIAC SOCIETY <b><?php echo e($conferenceYear); ?></b></span>
            <h1><?php echo e($conferenceTitle); ?></h1>
            <p><?php echo e($conferenceIntro); ?></p>
            <div class="conf-index-hero-actions">
                <a class="conf-index-button conf-index-button-bright" href="<?php echo SITE_URL; ?>/conference/registration">Register now <span aria-hidden="true">&#8599;</span></a>
                <a class="conf-index-button conf-index-button-glass" href="<?php echo SITE_URL; ?>/conference/call-for-abstracts">Submit an abstract <span aria-hidden="true">&#8594;</span></a>
            </div>
        </div>
        <div class="conf-index-hero-note"><span class="conf-index-pulse"></span><span>Evidence. Collaboration. Better heart health.</span></div>
    </div>
    <a class="conf-index-scroll" href="#conference-background" aria-label="Scroll to conference background"><span></span> DISCOVER</a>
</section>

<main class="conf-index-page">
    <section class="conf-index-background" id="conference-background">
        <div class="container conf-index-background-grid">
            <div class="conf-index-section-label"><span>01 / THE CONFERENCE</span><i></i><span>OUR PURPOSE</span></div>
            <div class="conf-index-background-copy">
                <h2>Better heart health begins when we move <em>forward together.</em></h2>
                <div class="conf-index-richtext">
                    <?php if ($backgroundText !== ''): ?>
                        <?php echo $backgroundText; ?>
                    <?php else: ?>
                        <p>The Somali Cardiac Society Conference brings clinicians, researchers, educators, institutions, and partners together around one shared ambition: stronger cardiovascular care for every community.</p>
                        <p>Across focused learning, scientific exchange, and practical collaboration, we turn regional experience into ideas that can improve care and shape healthier futures.</p>
                    <?php endif; ?>
                </div>
                <a class="conf-index-text-link" href="#conference-background">Explore the conference <span aria-hidden="true">&#8594;</span></a>
            </div>
            <div class="conf-index-background-mark" aria-hidden="true"><span>SCS</span><i></i></div>
        </div>
    </section>

    <section class="conf-index-lead-section">
        <div class="container conf-index-lead-grid">
            <div class="conf-index-lead-visual">
                <?php if ($headPhoto): ?>
                    <img src="<?php echo UPLOADS_URL . '/' . e($headPhoto); ?>" alt="<?php echo e($headName ?: 'Head of Conference'); ?>">
                <?php else: ?>
                    <div class="conf-index-lead-initials" aria-label="Conference leadership portrait placeholder">SCS</div>
                <?php endif; ?>
                <div class="conf-index-lead-caption"><span>CONFERENCE LEADERSHIP</span><strong><?php echo e($headName ?: 'A shared regional vision'); ?></strong></div>
                <div class="conf-index-lead-visual-accent" aria-hidden="true"></div>
            </div>
            <div class="conf-index-lead-copy">
                <span class="conf-index-kicker">A MESSAGE FROM THE CONFERENCE HEAD</span>
                <h2>Welcome to a meeting of <em>minds and purpose.</em></h2>
                <div class="conf-index-quote-mark" aria-hidden="true">“</div>
                <div class="conf-index-lead-message">
                    <?php if ($headMessage !== ''): ?>
                        <?php echo $headMessage; ?>
                    <?php else: ?>
                        <p>We are pleased to welcome colleagues and partners to this year’s conference. Together, we can share knowledge, strengthen professional connections, and advance cardiovascular care across our region.</p>
                    <?php endif; ?>
                </div>
                <?php if ($headName): ?><div class="conf-index-signature"><span></span><strong><?php echo e($headName); ?></strong><small>Head of Conference</small></div><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="conf-index-objectives">
        <div class="container">
            <div class="conf-index-section-heading">
                <div><span class="conf-index-kicker">02 / WHAT WE’RE HERE TO DO</span><h2>Focused on progress<br><em>that matters.</em></h2></div>
                <p>Our objectives keep the conversation grounded in practical learning, regional partnership, and better outcomes for patients.</p>
            </div>
            <div class="conf-index-objective-grid">
                <?php foreach ($objectives as $index => $objective): ?>
                    <?php $objectiveIcons = ['ph-lightbulb', 'ph-users-three', 'ph-heartbeat', 'ph-chart-line-up']; $icon = $objectiveIcons[$index % count($objectiveIcons)]; ?>
                    <article class="conf-index-objective">
                        <div class="conf-index-objective-top"><i class="ph <?php echo $icon; ?>" aria-hidden="true"></i><span><?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?></span></div>
                        <h3><?php echo e($objective); ?></h3>
                        <p>Turning shared expertise into meaningful progress for cardiovascular health.</p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="conf-index-attend">
        <div class="container">
            <div class="conf-index-section-heading conf-index-attend-heading">
                <div><span class="conf-index-kicker">03 / WHO SHOULD ATTEND</span><h2>There’s a place for<br><em>your perspective.</em></h2></div>
                <p>Cardiovascular progress takes a whole community. Join the people learning, caring, researching, and building what comes next.</p>
            </div>
            <div class="conf-index-attendee-grid">
                <?php foreach ($attendees as $index => $attendee): ?>
                    <?php
                    $attendeeKey = strtolower((string)$attendee);
                    $attendeeMeta = ['users-three', 'Regional insight', 'Bring your experience and ideas into the conversation.'];
                    foreach ($attendeeDetails as $match => $details) {
                        if (strpos($attendeeKey, $match) !== false) { $attendeeMeta = $details; break; }
                    }
                    ?>
                    <article class="conf-index-attendee">
                        <div class="conf-index-attendee-icon"><i class="ph ph-<?php echo e($attendeeMeta[0]); ?>" aria-hidden="true"></i></div>
                        <div class="conf-index-attendee-copy"><span><?php echo e($attendeeMeta[1]); ?></span><h3><?php echo e($attendee); ?></h3><p><?php echo e($attendeeMeta[2]); ?></p></div>
                        <span class="conf-index-attendee-number"><?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<?php if (!empty($pastConfs)): ?>
<section class="conf-index-archive">
    <div class="container">
        <div class="conf-index-section-heading">
            <div><span class="conf-index-kicker">A LOOK BACK</span><h2>Progress is a story<br><em>we build together.</em></h2></div>
            <a class="conf-index-text-link" href="<?php echo SITE_URL; ?>/conference/past">Explore the archive <span aria-hidden="true">&#8594;</span></a>
        </div>
        <div class="conf-index-archive-grid">
            <?php foreach ($pastConfs as $past):
                $gallery = $db->prepare("SELECT image_path FROM past_conference_gallery WHERE past_conference_id = :id ORDER BY display_order ASC, id ASC LIMIT 1");
                $gallery->execute([':id' => $past['id']]);
                $thumbUrl = $gallery->fetchColumn();
                $dateStr = !empty($past['conference_date'])
                    ? date('F Y', strtotime($past['conference_date']))
                    : 'SCS CONFERENCE ARCHIVE';
                $excerpt = !empty($past['body']) ? mb_substr(trim(strip_tags($past['body'])), 0, 125) : 'Revisit the conversations, connections, and shared work from this gathering.';
            ?>
            <article class="conf-index-archive-card">
                <div class="conf-index-archive-image">
                    <?php if ($thumbUrl): ?>
                        <img src="<?php echo e($thumbUrl); ?>"
                             alt="<?php echo e($past['title']); ?>" loading="lazy">
                    <?php else: ?>
                        <div class="conf-index-archive-placeholder"><i class="ph ph-heartbeat" aria-hidden="true"></i></div>
                    <?php endif; ?>
                    <span><?php echo e($dateStr); ?></span>
                </div>
                <div class="conf-index-archive-copy">
                    <h3><?php echo e($past['title']); ?></h3>
                    <p><?php echo e($excerpt); ?><?php echo mb_strlen(trim(strip_tags($past['body'] ?? ''))) > 125 ? '…' : ''; ?></p>
                    <a href="<?php echo SITE_URL; ?>/conference/past-detail?slug=<?php echo rawurlencode($past['slug']); ?>">View conference <span aria-hidden="true">&#8594;</span></a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
