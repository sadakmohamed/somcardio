<?php
require_once __DIR__ . '/../config/auth.php';

$db = getDB();

$conf = $db->query("SELECT * FROM conferences WHERE status = 'active' ORDER BY id DESC LIMIT 1")->fetch();
if (!$conf) {
    $conf = ['id' => 0, 'title' => 'Somali Cardiac Society Annual Conference', 'year' => date('Y'), 'hero_image' => ''];
}

$settings = null;
if ($conf['id']) {
    $settingsStmt = $db->prepare("SELECT * FROM conference_abstract_settings WHERE conference_id = :cid LIMIT 1");
    $settingsStmt->execute([':cid' => $conf['id']]);
    $settings = $settingsStmt->fetch();
}
$deadline = $settings['submission_deadline'] ?? $conf['abstract_deadline'] ?? null;
$deadlineTimestamp = $deadline ? strtotime($deadline) : null;

$subthemes = [];
if ($conf['id']) {
    $subStmt = $db->prepare("SELECT * FROM conference_subthemes WHERE conference_id = :cid AND is_active = 1 ORDER BY display_order ASC, id ASC");
    $subStmt->execute([':cid' => $conf['id']]);
    $subthemes = $subStmt->fetchAll();
}

$criteria = [];
if ($conf['id']) {
    $critStmt = $db->prepare("SELECT * FROM conference_evaluation_criteria WHERE conference_id = :cid AND is_active = 1 ORDER BY display_order ASC, id ASC");
    $critStmt->execute([':cid' => $conf['id']]);
    $criteria = $critStmt->fetchAll();
}

$formatRequirements = !empty($settings['format_requirements']) ? $settings['format_requirements'] : "<ul><li>Abstract length: 250–400 words</li><li>Use structured headings: Background, Methods, Results, Conclusion</li><li>Include 3–5 keywords and author affiliations</li><li>Submit as PDF in A4 format</li></ul>";
$structure = !empty($settings['structure']) ? $settings['structure'] : "<ol><li>Title</li><li>Background</li><li>Objective</li><li>Methods</li><li>Results</li><li>Conclusion</li><li>Keywords</li></ol>";
$pageTitle = 'Call for Abstracts';
$pageDescription = 'Submit an abstract for the annual Somali Cardiac Society conference.';
$submissionSuccess = getFlash('success');
$submissionError = getFlash('error');

require_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/conference.css?v=3.8">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<div class="conf-detail-nav-wrap"><?php include __DIR__ . '/../includes/conference_subnav.php'; ?></div>
<?php if ($submissionSuccess || $submissionError): ?>
    <div class="conf-submission-alert-wrap container" role="<?php echo $submissionError ? 'alert' : 'status'; ?>" aria-live="polite">
        <div class="conf-submission-alert <?php echo $submissionError ? 'is-error' : 'is-success'; ?>">
            <i class="ph <?php echo $submissionError ? 'ph-warning-circle' : 'ph-check-circle'; ?>" aria-hidden="true"></i>
            <p><?php echo e($submissionError ?: $submissionSuccess); ?></p>
            <button type="button" class="conf-submission-alert-close" aria-label="Dismiss message"><i class="ph ph-x" aria-hidden="true"></i></button>
        </div>
    </div>
<?php endif; ?>

<section class="conf-abstract-hero conf-abstract-hero-simple" style="--abstract-hero-image:url('<?php echo !empty($conf['hero_image']) ? UPLOADS_URL . '/' . e($conf['hero_image']) : SITE_URL . '/images/hero2.png'; ?>')">
    <div class="container conf-abstract-hero-inner">
        <div class="conf-abstract-hero-copy">
            <span class="conf-abstract-eyebrow"><i></i> 2026 / CALL FOR ABSTRACTS</span>
            <h1>Share the research<br><em>shaping cardiovascular care</em></h1>
            <p>Submit your original research and clinical work for consideration at the 2nd National Cardiac Conference 2026</p>
        </div>
        <aside class="conf-deadline-card<?php echo $deadlineTimestamp && $deadlineTimestamp < time() ? ' is-closed' : ''; ?>" id="countdownBox" <?php if ($deadlineTimestamp && $deadlineTimestamp > time()): ?>data-deadline="<?php echo (int)$deadlineTimestamp * 1000; ?>"<?php endif; ?>>
            <div class="conf-deadline-card-top"><span class="conf-deadline-pulse"></span><span>ABSTRACT SUBMISSION DEADLINE</span><i class="ph ph-hourglass-medium" aria-hidden="true"></i></div>
            <?php if ($deadlineTimestamp && $deadlineTimestamp > time()): ?>
                <div class="conf-deadline-date"><?php echo e(date('j F Y', $deadlineTimestamp)); ?></div>
                <div class="conf-deadline-count" aria-live="polite">
                    <div><strong data-count-days>--</strong><span>Days</span></div><b>:</b>
                    <div><strong data-count-hours>--</strong><span>Hours</span></div><b>:</b>
                    <div><strong data-count-minutes>--</strong><span>Minutes</span></div><b>:</b>
                    <div><strong data-count-seconds>--</strong><span>Seconds</span></div>
                </div>
                <p class="conf-deadline-foot">Time remaining to submit your abstract</p>
            <?php elseif ($deadlineTimestamp): ?>
                <div class="conf-deadline-closed"><i class="ph ph-lock-key" aria-hidden="true"></i><strong>Submissions are closed</strong><span>The abstract deadline has passed.</span></div>
            <?php else: ?>
                <div class="conf-deadline-date">15 October 2026</div>
                <p class="conf-deadline-foot">Time remaining to submit your abstract</p>
            <?php endif; ?>
        </aside>
    </div>
</section>

<main class="conf-abstract-page">
<section class="conf-abstract-guidelines">
    <div class="container">
        <div class="conf-abstract-section-heading">
            <div><span class="conf-abstract-kicker">BEFORE YOU BEGIN</span><h2>Abstract guidelines</h2></div>
            <p>Use the format and structure set by the scientific committee to prepare your submission.</p>
        </div>
        <div class="conf-guideline-pair">
            <article class="conf-guideline-card"><div class="conf-guideline-card-icon"><i class="ph ph-file-pdf" aria-hidden="true"></i></div><span class="conf-guideline-label">01 / FORMAT REQUIREMENT</span><h3>Format requirements</h3><div class="conf-guideline-richtext"><?php echo $formatRequirements; ?></div></article>
            <article class="conf-guideline-card"><div class="conf-guideline-card-icon"><i class="ph ph-list-numbers" aria-hidden="true"></i></div><span class="conf-guideline-label">02 / ABSTRACT STRUCTURE</span><h3>Structure</h3><div class="conf-guideline-richtext"><?php echo $structure; ?></div></article>
        </div>
    </div>
</section>

<section class="conf-abstract-criteria">
    <div class="container">
        <div class="conf-abstract-section-heading">
            <div><span class="conf-abstract-kicker">SCIENTIFIC REVIEW</span><h2>Evaluation Criteria</h2></div>
            <p>Submissions are evaluated by the Scientific Committee according to scientific quality, relevance, originality, methodology, and potential impact on cardiovascular care.</p>
        </div>
        <?php if ($criteria): ?>
            <div class="conf-criteria-grid">
                <?php foreach ($criteria as $index => $criterion): ?>
                    <article class="conf-criteria-card"><span class="conf-criteria-number"><?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?></span><span class="conf-criteria-icon"><i class="ph <?php echo ['ph-check-circle','ph-flask','ph-target','ph-lightbulb'][$index % 4]; ?>" aria-hidden="true"></i></span><h3><?php echo e($criterion['title']); ?></h3><p><?php echo !empty($criterion['detail']) ? e($criterion['detail']) : 'The scientific committee considers this when assessing the quality and relevance of each submission.'; ?></p></article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="conf-abstract-empty"><i class="ph ph-clipboard-text" aria-hidden="true"></i><p>The scientific committee’s evaluation criteria will be published here soon.</p></div>
        <?php endif; ?>
    </div>
</section>

<section class="conf-abstract-subthemes">
    <div class="container">
        <div class="conf-abstract-section-heading">
            <div><span class="conf-abstract-kicker">SELECT A SCIENTIFIC THEME</span><h2>Submit your abstract</h2></div>
            <p>Choose the scientific theme that best fits your research, then complete the submission form below</p>
        </div>
        <?php if ($subthemes): ?>
            <div class="conf-subtheme-grid" id="abstractSubthemeOptions" role="radiogroup" aria-label="Select an abstract subtheme">
                <?php $themeIcons = ['ph-heartbeat','ph-microscope','ph-first-aid-kit','ph-chart-line-up','ph-users-three','ph-stethoscope']; ?>
                <?php foreach ($subthemes as $index => $subtheme): ?>
                    <button type="button" class="conf-subtheme-card" role="radio" aria-checked="false" data-subtheme-id="<?php echo (int)$subtheme['id']; ?>" data-subtheme-title="<?php echo e($subtheme['title']); ?>">
                        <span class="conf-subtheme-card-top"><span><i class="ph <?php echo $themeIcons[$index % count($themeIcons)]; ?>" aria-hidden="true"></i></span><small><?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?></small></span>
                        <span class="conf-subtheme-card-copy"><strong><?php echo e($subtheme['title']); ?></strong><span><?php echo !empty($subtheme['detail']) ? e($subtheme['detail']) : 'Research, clinical insight, and practical discussion in this area are welcomed.'; ?></span></span>
                        <span class="conf-subtheme-card-check"><i class="ph ph-check" aria-hidden="true"></i></span>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="conf-abstract-empty"><i class="ph ph-sparkle" aria-hidden="true"></i><p>No subthemes are currently listed. Contact conference@somcardio.so for submission guidance.</p></div>
        <?php endif; ?>
    </div>
</section>

<section class="conf-abstract-submit" id="abstract-submission">
    <div class="container">
        <div class="conf-abstract-form-card">
            <div class="conf-abstract-form-top"><div><span>2ND NATIONAL CARDIAC CONFERENCE / 2026</span><h2>Abstract submission form</h2></div><i class="ph ph-paper-plane-tilt" aria-hidden="true"></i></div>
            <form action="<?php echo SITE_URL; ?>/conference/abstract-action" method="post" enctype="multipart/form-data" class="conf-abstract-form">
                <?php echo csrfField(); ?>
                <div class="conf-selected-subtheme" id="selectedSubthemeSummary" hidden><i class="ph ph-check-circle" aria-hidden="true"></i><span>Selected scientific theme:</span><strong id="selectedSubthemeLabel"></strong><a href="#abstractSubthemeOptions">Change</a></div>
                <div class="conf-abstract-form-grid">
                    <div class="form-group">
                        <label for="abstractFullName">1 Full name <b>*</b></label>
                        <input id="abstractFullName" type="text" name="full_name" placeholder="Name of the submitting/presenting author" autocomplete="name" required>
                    </div>
                    <div class="form-group">
                        <label for="abstractEmail">2 Email address <b>*</b></label>
                        <input id="abstractEmail" type="email" name="email" placeholder="you@example.com" autocomplete="email" required>
                    </div>
                    <div class="form-group">
                        <label for="abstractOrganization">3 Institution / Organization <b>*</b></label>
                        <input id="abstractOrganization" type="text" name="organization" placeholder="Hospital, university, or organization" autocomplete="organization" required>
                    </div>
                    <div class="form-group">
                        <label for="abstractTitle">4 Abstract title <b>*</b></label>
                        <input id="abstractTitle" type="text" name="title" placeholder="A concise title for your research" required>
                    </div>
                    <div class="form-group">
                        <label for="abstractSubmissionType">5 Submission Type <b>*</b></label>
                        <select id="abstractSubmissionType" name="submission_type" required>
                            <option value="Original Research">Original Research</option>
                            <option value="Case Report or Case Series">Case Report or Case Series</option>
                            <option value="Quality Improvement">Quality Improvement</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="selectedSubthemeId">6 Scientific Theme <b>*</b></label>
                        <select id="selectedSubthemeId" name="subtheme_id" required>
                            <option value="">-- Choose a scientific theme --</option>
                            <?php foreach ($subthemes as $theme): ?>
                                <option value="<?php echo (int)$theme['id']; ?>"><?php echo e($theme['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group conf-abstract-file-field" style="grid-column:1/-1;">
                        <label for="abstractFile">7 Abstract PDF <b>*</b></label>
                        <input id="abstractFile" type="file" name="abstract_file" accept="application/pdf,.pdf" required>
                        <small>Upload the complete abstract in PDF format according to the submission guidelines.</small>
                    </div>
                </div>
                <div class="conf-abstract-form-footer">
                    <span><i class="ph ph-lock-key" aria-hidden="true"></i> 🔒 Your abstract and submission details are transmitted securely</span>
                    <button type="submit" class="conf-abstract-submit-button">Send abstract <i class="ph ph-arrow-up-right" aria-hidden="true"></i></button>
                </div>
            </form>
        </div>
        <p class="conf-abstract-help-line">Need help with your submission? <a href="mailto:conference@somcardio.so">Contact conference@somcardio.so</a></p>
    </div>
</section>
</main>

<script>
(function () {
    var box = document.getElementById('countdownBox');
    if (!box || !box.dataset.deadline) return;
    var deadline = Number(box.dataset.deadline);
    var timer;
    function renderCountdown() {
        var remaining = deadline - Date.now();
        if (remaining <= 0) {
            window.clearInterval(timer);
            box.classList.add('is-closed');
            box.innerHTML = '<div class="conf-deadline-card-top"><span class="conf-deadline-pulse"></span><span>ABSTRACT DEADLINE</span><i class="ph ph-lock-key" aria-hidden="true"></i></div><div class="conf-deadline-closed"><i class="ph ph-lock-key" aria-hidden="true"></i><strong>Submissions are closed</strong><span>The abstract deadline has passed.</span></div>';
            return;
        }
        var units = {
            days: Math.floor(remaining / 86400000),
            hours: Math.floor((remaining % 86400000) / 3600000),
            minutes: Math.floor((remaining % 3600000) / 60000),
            seconds: Math.floor((remaining % 60000) / 1000)
        };
        Object.keys(units).forEach(function (unit) {
            var target = box.querySelector('[data-count-' + unit + ']');
            if (target) target.textContent = String(units[unit]).padStart(2, '0');
        });
    }
    renderCountdown();
    timer = window.setInterval(renderCountdown, 1000);
}());

 (function () {
    var options = document.getElementById('abstractSubthemeOptions');
    var form = document.querySelector('.conf-abstract-form');
    var selectedId = document.getElementById('selectedSubthemeId');
    var selectedSummary = document.getElementById('selectedSubthemeSummary');
    var selectedLabel = document.getElementById('selectedSubthemeLabel');
    if (options && form && selectedId) {
        var cards = Array.prototype.slice.call(options.querySelectorAll('[data-subtheme-id]'));
        function selectTheme(card) {
            cards.forEach(function (item) {
                var active = item === card;
                item.classList.toggle('is-selected', active);
                item.setAttribute('aria-checked', active ? 'true' : 'false');
            });
            selectedId.value = card.dataset.subthemeId;
            selectedLabel.textContent = card.dataset.subthemeTitle;
            selectedSummary.hidden = false;
        }
        selectedId.addEventListener('change', function () {
            var val = this.value;
            cards.forEach(function (item) {
                var active = item.dataset.subthemeId === val;
                item.classList.toggle('is-selected', active);
                item.setAttribute('aria-checked', active ? 'true' : 'false');
            });
            var opt = this.options[this.selectedIndex];
            if (opt && opt.text && val) {
                selectedLabel.textContent = opt.text;
                selectedSummary.hidden = false;
            } else {
                selectedSummary.hidden = true;
            }
        });
        cards.forEach(function (card, index) {
            card.addEventListener('click', function () { selectTheme(card); });
            card.addEventListener('keydown', function (event) {
                if (event.key === 'ArrowRight' || event.key === 'ArrowDown' || event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    var direction = event.key === 'ArrowRight' || event.key === 'ArrowDown' ? 1 : -1;
                    var next = cards[(index + direction + cards.length) % cards.length];
                    selectTheme(next);
                    next.focus();
                }
            });
        });
        form.addEventListener('submit', function (event) {
            if (cards.length && !selectedId.value) {
                event.preventDefault();
                options.setAttribute('aria-describedby', 'subthemeSelectionError');
                var error = document.getElementById('subthemeSelectionError');
                if (!error) {
                    error = document.createElement('p');
                    error.id = 'subthemeSelectionError';
                    error.className = 'conf-subtheme-selection-error';
                    error.setAttribute('role', 'alert');
                    error.textContent = 'Select a scientific subtheme before submitting your abstract.';
                    options.parentNode.insertBefore(error, options.nextSibling);
                }
                options.scrollIntoView({ behavior: 'smooth', block: 'center' });
                cards[0].focus({ preventScroll: true });
            }
        });
    }
}());

document.querySelectorAll('.conf-submission-alert-close').forEach(function (button) {
    button.addEventListener('click', function () {
        var alert = button.closest('.conf-submission-alert');
        if (alert) alert.remove();
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
