<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$conf = $db->query("SELECT * FROM conferences WHERE status = 'active' ORDER BY id DESC LIMIT 1")->fetch();
$settings = null;
if ($conf) {
    $st = $db->prepare("SELECT * FROM conference_registration_settings WHERE conference_id = :cid LIMIT 1");
    $st->execute([':cid' => $conf['id']]);
    $settings = $st->fetch();
}

$pageTitle = 'Conference Registration';
$pageDescription = 'Register for the Somali Cardiac Society annual conference.';

?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/conference.css?v=3.8">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<div class="conf-detail-nav-wrap"><?php include __DIR__ . '/../includes/conference_subnav.php'; ?></div>

<section class="conf-registration-hero" style="--registration-hero-image:url('<?php echo !empty($conf['hero_image']) ? UPLOADS_URL . '/' . e($conf['hero_image']) : SITE_URL . '/images/hero2.png'; ?>')">
    <div class="container conf-registration-hero-inner">
        <div class="conf-registration-hero-copy">
            <span class="conf-registration-eyebrow"><i></i> <?php echo e($conf['year'] ?? date('Y')); ?> / ATTENDEE REGISTRATION</span>
            <h1>Make room for<br><em>what’s next.</em></h1>
            <p>Join the people advancing cardiovascular care through shared knowledge, meaningful connections, and fresh ideas.</p>
            <a href="#registration-plans" class="conf-registration-scroll">Find your registration <span aria-hidden="true">&#8595;</span></a>
        </div>
        <div class="conf-registration-hero-mark"><i class="ph ph-heartbeat" aria-hidden="true"></i><span>SOMALI CARDIAC<br>SOCIETY</span></div>
    </div>
</section>

<main class="conf-registration-page">
<section class="conf-registration-plans" id="registration-plans">
    <div class="container">
        <div class="conf-registration-section-heading">
            <div><span class="conf-registration-kicker">01 / CHOOSE YOUR PASS</span><h2>Find your place<br><em>in the room.</em></h2></div>
            <p>Select the category that best describes you. We’ll carry your choice into the registration form.</p>
        </div>
        <div class="conf-registration-plan-grid" role="radiogroup" aria-label="Registration category">
            <button type="button" class="conf-registration-plan" data-category="student" aria-checked="false" role="radio"><span class="conf-registration-plan-icon"><i class="ph ph-graduation-cap" aria-hidden="true"></i></span><span class="conf-registration-plan-label">STUDENT / TRAINEE</span><strong>$30</strong><span class="conf-registration-plan-detail">For students and early-career clinicians.</span><span class="conf-registration-plan-select">Select pass <i class="ph ph-arrow-right" aria-hidden="true"></i></span></button>
            <button type="button" class="conf-registration-plan is-selected" data-category="professional" aria-checked="true" role="radio"><span class="conf-registration-plan-featured">MOST SELECTED</span><span class="conf-registration-plan-icon"><i class="ph ph-stethoscope" aria-hidden="true"></i></span><span class="conf-registration-plan-label">HEALTH PROFESSIONAL</span><strong>$60</strong><span class="conf-registration-plan-detail">For clinicians, researchers, and care teams.</span><span class="conf-registration-plan-select">Selected <i class="ph ph-check" aria-hidden="true"></i></span></button>
            <button type="button" class="conf-registration-plan" data-category="ingo" aria-checked="false" role="radio"><span class="conf-registration-plan-icon"><i class="ph ph-handshake" aria-hidden="true"></i></span><span class="conf-registration-plan-label">INSTITUTION / PARTNER</span><strong>$90</strong><span class="conf-registration-plan-detail">For NGO, institutional, and partner delegates.</span><span class="conf-registration-plan-select">Select pass <i class="ph ph-arrow-right" aria-hidden="true"></i></span></button>
        </div>
    </div>
</section>

<section class="conf-registration-form-section">
    <div class="container conf-registration-form-layout">
        <aside class="conf-registration-aside">
            <span class="conf-registration-kicker">02 / YOUR DETAILS</span>
            <h2>Registration,<br><em>made simple.</em></h2>
            <p>Complete each short step. You can review everything before sending your application.</p>
            <div class="conf-registration-assist"><span class="conf-registration-assist-icon"><i class="ph ph-chat-circle-dots" aria-hidden="true"></i></span><div><strong>Need a hand?</strong><p>Our team can help with registration or payment questions.</p><a href="mailto:<?php echo e(CONTACT_EMAIL); ?>"><?php echo e(CONTACT_EMAIL); ?> <span aria-hidden="true">&#8599;</span></a></div></div>
        </aside>
        <div class="conf-registration-form-card">
            <div class="conf-registration-form-heading"><span>ATTENDEE APPLICATION</span><strong><?php echo e($conf['year'] ?? date('Y')); ?></strong></div>
            <div class="conf-registration-form-body">
            <h2>Reserve your place</h2>
            <p class="conf-registration-form-intro">Fields marked with <b>*</b> are required.</p>
            <div class="conf-stepper" aria-label="Registration progress">
                <div class="conf-step active" data-step-indicator="1"><span class="conf-step-number">01</span><span>About you</span></div>
                <div class="conf-step" data-step-indicator="2"><span class="conf-step-number">02</span><span>Payment</span></div>
                <div class="conf-step" data-step-indicator="3"><span class="conf-step-number">03</span><span>Confirm</span></div>
            </div>
            <form id="conferenceRegistrationForm" action="<?php echo SITE_URL; ?>/conference/register-action" method="post" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <div class="conf-step-panel active" data-step-panel="1">
                    <div class="conf-form-grid">
                    <div class="form-group">
                        <label for="registrationFirstName">First name <b>*</b></label>
                        <input id="registrationFirstName" type="text" name="first_name" placeholder="e.g. Amina" autocomplete="given-name" required>
                    </div>
                    <div class="form-group">
                        <label for="registrationLastName">Last name <b>*</b></label>
                        <input id="registrationLastName" type="text" name="last_name" placeholder="e.g. Hassan" autocomplete="family-name" required>
                    </div>
                    <div class="form-group">
                        <label for="registrationEmail">Email address <b>*</b></label>
                        <input id="registrationEmail" type="email" name="email" placeholder="you@example.com" autocomplete="email" required>
                    </div>
                    <div class="form-group">
                        <label for="registrationPhone">Phone number <b>*</b></label>
                        <input id="registrationPhone" type="tel" name="phone" placeholder="+252 61 000 0000" autocomplete="tel" required>
                    </div>
                    <div class="form-group">
                        <label for="registrationOrganization">Organization</label>
                        <input id="registrationOrganization" type="text" name="organization" placeholder="Hospital, university, or organization" autocomplete="organization">
                    </div>
                    <div class="form-group">
                        <label for="registrationCountry">Country <b>*</b></label>
                        <input id="registrationCountry" type="text" name="country" placeholder="Your country of residence" autocomplete="country-name" required>
                    </div>
                    </div>
                    <select class="conf-visually-hidden" name="category" id="registrationCategory" aria-label="Registration category" required tabindex="-1"><option value="student">Student</option><option value="professional" selected>Professional</option><option value="ingo">I/NGO</option></select>
                    <div class="conf-step-actions"><span></span><button type="button" class="btn-conf-primary" data-next-step="2">Continue to payment <span aria-hidden="true">&#8594;</span></button></div>
                </div>

                <div class="conf-step-panel" data-step-panel="2">
                    <div class="conf-registration-payment-box">
                        <span class="conf-registration-kicker">PAYMENT INFORMATION</span>
                        <strong><?php echo e($settings['bank_name'] ?? 'Somali Bank'); ?></strong>
                        <span>Account <?php echo e($settings['account_number'] ?? 'details will be provided by our team'); ?></span>
                        <small><?php echo e($settings['process_text'] ?? 'Follow the payment instructions and attach your confirmation below.'); ?></small>
                    </div>
                    <div class="conf-form-grid">
                    <div class="form-group conf-student-document" hidden>
                        <label for="studentIdUpload">Student ID / proof of enrollment <b>*</b></label>
                        <input id="studentIdUpload" type="file" name="student_id_path" accept="image/*,.pdf">
                        <small>Required for the student / trainee pass.</small>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label for="paymentScreenshot">Payment confirmation</label>
                        <input id="paymentScreenshot" type="file" name="payment_screenshot" accept="image/*,.pdf">
                        <small>Attach a receipt or screenshot if payment is already complete.</small>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label for="paymentPhone">Payment phone / mobile wallet</label>
                        <input id="paymentPhone" type="tel" name="payment_phone" placeholder="Number used for EVC, Zaad, or mobile payment">
                    </div>
                    </div>
                    <div class="conf-step-actions"><button type="button" class="btn-conf-dark" data-prev-step="1"><span aria-hidden="true">&#8592;</span> Back</button><button type="button" class="btn-conf-primary" data-next-step="3">Review application <span aria-hidden="true">&#8594;</span></button></div>
                </div>

                <div class="conf-step-panel" data-step-panel="3">
                    <div class="conf-registration-review">
                        <div><span>Attendee</span><strong data-review="name">—</strong></div>
                        <div><span>Email</span><strong data-review="email">—</strong></div>
                        <div><span>Registration pass</span><strong data-review="category">—</strong></div>
                        <div><span>Payment attachment</span><strong data-review="payment">Not attached</strong></div>
                    </div>
                    <div class="conf-registration-review-note"><i class="ph ph-shield-check" aria-hidden="true"></i><p>Your details will be sent securely to the conference team for review. You can go back to edit before confirming.</p></div>
                    <div class="conf-step-actions"><button type="button" class="btn-conf-dark" data-prev-step="2"><span aria-hidden="true">&#8592;</span> Edit details</button><button type="submit" class="btn-conf-primary conf-registration-submit">Confirm registration <span aria-hidden="true">&#8599;</span></button></div>
                </div>
            </form>
        </div>
        </div>
    </div>
</section>
</main>

<dialog class="conf-registration-dialog" id="registrationConfirmDialog" aria-labelledby="registrationDialogTitle">
    <div class="conf-registration-dialog-mark"><i class="ph ph-paper-plane-tilt" aria-hidden="true"></i></div>
    <span class="conf-registration-kicker">READY TO SEND</span>
    <h2 id="registrationDialogTitle">Confirm your place?</h2>
    <p>Your registration details are ready to be sent to the Somali Cardiac Society conference team.</p>
    <div class="conf-registration-dialog-actions"><button type="button" class="btn-conf-dark" id="registrationDialogBack">Review again</button><button type="button" class="btn-conf-primary" id="registrationDialogConfirm">Yes, submit <span aria-hidden="true">&#8599;</span></button></div>
</dialog>

<script>
(function () {
    var form = document.getElementById('conferenceRegistrationForm');
    if (!form) return;
    var current = 1;
    var panels = form.querySelectorAll('[data-step-panel]');
    var indicators = document.querySelectorAll('[data-step-indicator]');
    var categorySelect = document.getElementById('registrationCategory');
    var studentDocument = form.querySelector('.conf-student-document');
    var studentId = document.getElementById('studentIdUpload');
    var confirmDialog = document.getElementById('registrationConfirmDialog');
    var dialogConfirm = document.getElementById('registrationDialogConfirm');
    var submitButton = form.querySelector('.conf-registration-submit');
    var categoryNames = { student: 'Student / Trainee', professional: 'Health Professional', ingo: 'Institution / Partner' };

    function updateCategory(category) {
        categorySelect.value = category;
        document.querySelectorAll('.conf-registration-plan').forEach(function (plan) {
            var selected = plan.dataset.category === category;
            plan.classList.toggle('is-selected', selected);
            plan.setAttribute('aria-checked', selected ? 'true' : 'false');
            var status = plan.querySelector('.conf-registration-plan-select');
            if (status) status.innerHTML = selected ? 'Selected <i class="ph ph-check" aria-hidden="true"></i>' : 'Select pass <i class="ph ph-arrow-right" aria-hidden="true"></i>';
        });
        var isStudent = category === 'student';
        studentDocument.hidden = !isStudent;
        studentId.required = isStudent;
        studentId.disabled = !isStudent;
    }

    document.querySelectorAll('.conf-registration-plan').forEach(function (plan) {
        plan.addEventListener('click', function () { updateCategory(plan.dataset.category); });
        plan.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
                event.preventDefault();
                var plans = Array.prototype.slice.call(document.querySelectorAll('.conf-registration-plan'));
                var next = plans[(plans.indexOf(plan) + (event.key === 'ArrowRight' ? 1 : plans.length - 1)) % plans.length];
                updateCategory(next.dataset.category);
                next.focus();
            }
        });
    });

    function showStep(step) {
        current = step;
        panels.forEach(function (panel) { panel.classList.toggle('active', panel.dataset.stepPanel == step); });
        indicators.forEach(function (item) {
            var value = Number(item.dataset.stepIndicator);
            item.classList.toggle('active', value === step);
            item.classList.toggle('done', value < step);
        });
        form.closest('.conf-registration-form-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (step === 3) {
            form.querySelector('[data-review="name"]').textContent = (form.first_name.value + ' ' + form.last_name.value).trim();
            form.querySelector('[data-review="email"]').textContent = form.email.value;
            form.querySelector('[data-review="category"]').textContent = categoryNames[categorySelect.value];
            form.querySelector('[data-review="payment"]').textContent = form.payment_screenshot.files.length ? form.payment_screenshot.files[0].name : 'Not attached';
        }
    }
    form.querySelectorAll('[data-next-step]').forEach(function (button) {
        button.addEventListener('click', function () {
            var fields = form.querySelectorAll('[data-step-panel="' + current + '"] input, [data-step-panel="' + current + '"] select');
            var valid = true;
            fields.forEach(function (field) { if (!field.checkValidity()) { field.reportValidity(); valid = false; } });
            if (valid) showStep(Number(button.dataset.nextStep));
        });
    });
    form.querySelectorAll('[data-prev-step]').forEach(function (button) { button.addEventListener('click', function () { showStep(Number(button.dataset.prevStep)); }); });
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (confirmDialog && typeof confirmDialog.showModal === 'function') confirmDialog.showModal();
        else if (window.confirm('Submit your registration to the conference team?')) form.submit();
    });
    document.getElementById('registrationDialogBack').addEventListener('click', function () { confirmDialog.close(); });
    dialogConfirm.addEventListener('click', function () {
        dialogConfirm.disabled = true;
        dialogConfirm.innerHTML = '<i class="ph ph-spinner-gap" aria-hidden="true"></i> Sending registration...';
        submitButton.disabled = true;
        form.submit();
    });
    updateCategory(categorySelect.value);
}());
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
