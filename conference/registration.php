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
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/conference.css?v=3.9">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<div class="conf-detail-nav-wrap"><?php include __DIR__ . '/../includes/conference_subnav.php'; ?></div>

<!-- ─── HERO ─────────────────────────────────────────────────────────── -->
<section class="conf-registration-hero" style="--registration-hero-image:url('<?php echo !empty($conf['hero_image']) ? UPLOADS_URL . '/' . e($conf['hero_image']) : SITE_URL . '/images/hero2.png'; ?>')">
    <div class="container conf-registration-hero-inner">
        <div class="conf-registration-hero-copy">
            <span class="conf-registration-eyebrow"><i></i> <?php echo e($conf['year'] ?? date('Y')); ?> / ATTENDEE REGISTRATION</span>
            <h1>Join the people shaping<br><em>cardiovascular care</em></h1>
            <p>Register for the 2nd National Cardiac Conference 2026 and join cardiovascular professionals, researchers, healthcare leaders, students, and partners from across Somalia and beyond</p>
            <a href="#registration-plans" class="conf-registration-scroll">Register now <span aria-hidden="true">&#8595;</span></a>
        </div>
        <div class="conf-registration-hero-mark"><i class="ph ph-heartbeat" aria-hidden="true"></i><span>SOMALI CARDIAC<br>SOCIETY</span></div>
    </div>
</section>

<main class="conf-registration-page">

<!-- ─── 01 / CHOOSE YOUR REGISTRATION CATEGORY ───────────────────────── -->
<section class="conf-registration-plans" id="registration-plans">
    <div class="container">
        <div class="conf-registration-section-heading">
            <div>
                <span class="conf-registration-kicker">01 / CHOOSE YOUR REGISTRATION CATEGORY</span>
                <h2>Find your place<br><em>in the room.</em></h2>
            </div>
            <p>Select the category that best describes you. It will be applied to the registration form.</p>
        </div>
        <div class="conf-registration-plan-grid" role="radiogroup" aria-label="Registration category">
            <button type="button" class="conf-registration-plan" data-category="student" data-price="30" aria-checked="false" role="radio">
                <span class="conf-registration-plan-icon"><i class="ph ph-graduation-cap" aria-hidden="true"></i></span>
                <span class="conf-registration-plan-label">STUDENT / TRAINEE</span>
                <strong>$30</strong>
                <span class="conf-registration-plan-detail">For students, interns, residents, fellows, and other healthcare trainees</span>
                <span class="conf-registration-plan-select">Select category <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
            </button>
            <button type="button" class="conf-registration-plan" data-category="professional" data-price="60" aria-checked="false" role="radio">
                <span class="conf-registration-plan-icon"><i class="ph ph-stethoscope" aria-hidden="true"></i></span>
                <span class="conf-registration-plan-label">HEALTH PROFESSIONAL</span>
                <strong>$60</strong>
                <span class="conf-registration-plan-detail">For physicians, nurses, allied health professionals, researchers, and other healthcare professionals</span>
                <span class="conf-registration-plan-select">Select category <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
            </button>
            <button type="button" class="conf-registration-plan" data-category="ingo" data-price="90" aria-checked="false" role="radio">
                <span class="conf-registration-plan-icon"><i class="ph ph-handshake" aria-hidden="true"></i></span>
                <span class="conf-registration-plan-label">INSTITUTIONAL / PARTNER DELEGATE</span>
                <strong>$90</strong>
                <span class="conf-registration-plan-detail">For institutional, NGO, industry, and partner representatives</span>
                <span class="conf-registration-plan-select">Select category <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
            </button>
        </div>
    </div>
</section>

<!-- ─── WORKSHOP SECTION ─────────────────────────────────────────────── -->
<section class="conf-registration-workshops" id="workshop-section">
    <div class="container">
        <div class="conf-registration-section-heading">
            <div>
                <span class="conf-registration-kicker">01 / CHOOSE YOUR WORKSHOP TYPE</span>
                <h2>Find the training<br><em>that fits your interest</em></h2>
            </div>
            <p>Select the workshop that best matches your professional interest and complete the registration form</p>
        </div>
        <div class="conf-registration-workshop-badge">
            <i class="ph ph-calendar-check" aria-hidden="true"></i>
            DAY 2 &bull; 30 OCTOBER 2026 &bull; PRACTICAL TRAINING &bull; LIMITED PLACES AVAILABLE
        </div>
        <div class="conf-registration-workshop-grid" role="radiogroup" aria-label="Workshop selection">
            <button type="button" class="conf-registration-workshop-card" data-workshop="ecg_arrhythmia" data-workshop-price="30" aria-checked="false" role="radio">
                <span class="conf-registration-workshop-icon"><i class="ph ph-heartbeat" aria-hidden="true"></i></span>
                <div class="conf-registration-workshop-body">
                    <span class="conf-registration-workshop-label">ECG &amp; ARRHYTHMIA WORKSHOP</span>
                    <p>Practical training in ECG interpretation, rhythm recognition, and a structured approach to common arrhythmias in clinical practice</p>
                </div>
                <strong class="conf-registration-workshop-price">$30</strong>
                <span class="conf-registration-workshop-select">Select workshop <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
            </button>
            <button type="button" class="conf-registration-workshop-card" data-workshop="cardiovascular_emergency" data-workshop-price="30" aria-checked="false" role="radio">
                <span class="conf-registration-workshop-icon"><i class="ph ph-first-aid-kit" aria-hidden="true"></i></span>
                <div class="conf-registration-workshop-body">
                    <span class="conf-registration-workshop-label">CARDIOVASCULAR EMERGENCY WORKSHOP</span>
                    <p>Hands-on training in the immediate assessment and management of acute cardiovascular emergencies including ACS, cardiac arrest, and acute heart failure</p>
                </div>
                <strong class="conf-registration-workshop-price">$30</strong>
                <span class="conf-registration-workshop-select">Select workshop <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
            </button>
            <button type="button" class="conf-registration-workshop-card" data-workshop="cardiac_surgery" data-workshop-price="30" aria-checked="false" role="radio">
                <span class="conf-registration-workshop-icon"><i class="ph ph-knife" aria-hidden="true"></i></span>
                <div class="conf-registration-workshop-body">
                    <span class="conf-registration-workshop-label">CARDIAC SURGERY &amp; PERIOPERATIVE CARE WORKSHOP</span>
                    <p>A focused session on cardiac surgical principles, perioperative care pathways, and multidisciplinary team management for cardiac procedures</p>
                </div>
                <strong class="conf-registration-workshop-price">$30</strong>
                <span class="conf-registration-workshop-select">Select workshop <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
            </button>
        </div>
    </div>
</section>

<!-- ─── 02 / YOUR DETAILS ─────────────────────────────────────────────── -->
<section class="conf-registration-form-section">
    <div class="container conf-registration-form-layout">
        <aside class="conf-registration-aside">
            <span class="conf-registration-kicker">02 / YOUR DETAILS</span>
            <h2>Registration,<br><em>made simple.</em></h2>
            <p>Complete each short step. You can review everything before sending your application.</p>

            <!-- Summary card showing selected category / workshop -->
            <div class="conf-registration-summary-card" id="regSummaryCard" hidden>
                <div class="conf-registration-summary-row" id="summaryCategory" hidden>
                    <span>Category</span><strong id="summaryCategoryLabel">—</strong>
                </div>
                <div class="conf-registration-summary-row" id="summaryWorkshop" hidden>
                    <span>Workshop</span><strong id="summaryWorkshopLabel">—</strong>
                </div>
                <div class="conf-registration-summary-row conf-registration-summary-fee" id="summaryFeeRow" hidden>
                    <span>Registration fee</span><strong id="summaryFeeLabel">—</strong>
                </div>
            </div>

            <div class="conf-registration-assist">
                <span class="conf-registration-assist-icon"><i class="ph ph-chat-circle-dots" aria-hidden="true"></i></span>
                <div>
                    <strong>Need a hand?</strong>
                    <p>Our team can help with registration or payment questions.</p>
                    <a href="mailto:<?php echo e(CONTACT_EMAIL); ?>"><?php echo e(CONTACT_EMAIL); ?> <span aria-hidden="true">&#8599;</span></a>
                </div>
            </div>
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
                <!-- Hidden fields synced by JS -->
                <input type="hidden" name="category" id="registrationCategory" value="">
                <input type="hidden" name="workshop" id="registrationWorkshop" value="">

                <!-- ── Step 1: Personal Details ── -->
                <div class="conf-step-panel active" data-step-panel="1">
                    <div class="conf-form-grid">
                        <div class="form-group" style="grid-column:1/-1;">
                            <label for="registrationFullName">Full name <b>*</b></label>
                            <input id="registrationFullName" type="text" name="full_name" placeholder="e.g. Amina Hassan" autocomplete="name" required>
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
                            <label for="registrationOrganization">Organization / Institution <b>*</b></label>
                            <input id="registrationOrganization" type="text" name="organization" placeholder="Hospital, university, or organization" autocomplete="organization" required>
                        </div>
                        <div class="form-group">
                            <label for="registrationCountry">Country <b>*</b></label>
                            <input id="registrationCountry" type="text" name="country" placeholder="Your country of residence" autocomplete="country-name" required>
                        </div>
                        <div class="form-group" style="grid-column:1/-1;">
                            <label for="registrationProfCategory">Professional Category <b>*</b></label>
                            <select id="registrationProfCategory" name="professional_category" required>
                                <option value="" disabled selected>Select your professional category</option>
                                <option value="Medical Student">Medical Student</option>
                                <option value="Intern">Intern</option>
                                <option value="General Practitioner (GP)">General Practitioner (GP)</option>
                                <option value="Resident / Postgraduate Trainee">Resident / Postgraduate Trainee</option>
                                <option value="Other Healthcare Professional (Specify)">Other Healthcare Professional (Specify)</option>
                            </select>
                        </div>
                        <div class="form-group" id="profCategoryOtherWrap" style="grid-column:1/-1;" hidden>
                            <label for="registrationProfCategoryOther">Please specify your professional category <b>*</b></label>
                            <input id="registrationProfCategoryOther" type="text" name="professional_category_other" placeholder="Your professional category">
                        </div>
                    </div>
                    <div class="conf-step-actions"><span></span><button type="button" class="btn-conf-primary" data-next-step="2">Continue to payment <span aria-hidden="true">&#8594;</span></button></div>
                </div>

                <!-- ── Step 2: Payment ── -->
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

                <!-- ── Step 3: Confirm ── -->
                <div class="conf-step-panel" data-step-panel="3">
                    <div class="conf-registration-review">
                        <div><span>Attendee</span><strong data-review="name">—</strong></div>
                        <div><span>Email</span><strong data-review="email">—</strong></div>
                        <div><span>Registration category</span><strong data-review="category">—</strong></div>
                        <div><span>Workshop</span><strong data-review="workshop">—</strong></div>
                        <div><span>Registration fee</span><strong data-review="fee">—</strong></div>
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
    /* ── State ── */
    var selectedCategory = '';
    var selectedWorkshop  = '';
    var categoryPrices    = { student: 30, professional: 60, ingo: 90 };
    var workshopPrice     = 30;
    var categoryNames     = {
        student:      'Student / Trainee',
        professional: 'Health Professional',
        ingo:         'Institutional / Partner Delegate'
    };
    var workshopNames = {
        ecg_arrhythmia:        'ECG & Arrhythmia Workshop',
        cardiovascular_emergency: 'Cardiovascular Emergency Workshop',
        cardiac_surgery:       'Cardiac Surgery & Perioperative Care Workshop'
    };

    var form             = document.getElementById('conferenceRegistrationForm');
    var catInput         = document.getElementById('registrationCategory');
    var workshopInput    = document.getElementById('registrationWorkshop');
    var studentDocument  = form ? form.querySelector('.conf-student-document') : null;
    var studentId        = document.getElementById('studentIdUpload');
    var confirmDialog    = document.getElementById('registrationConfirmDialog');
    var dialogConfirm    = document.getElementById('registrationDialogConfirm');
    var submitButton     = form ? form.querySelector('.conf-registration-submit') : null;
    var profCatSelect    = document.getElementById('registrationProfCategory');
    var profCatOther     = document.getElementById('profCategoryOtherWrap');
    var profCatOtherIn   = document.getElementById('registrationProfCategoryOther');
    var current          = 1;
    var panels           = form ? form.querySelectorAll('[data-step-panel]') : [];
    var indicators       = document.querySelectorAll('[data-step-indicator]');

    /* ── Summary card ── */
    var summaryCard     = document.getElementById('regSummaryCard');
    var summaryCategory = document.getElementById('summaryCategory');
    var summaryCatLbl   = document.getElementById('summaryCategoryLabel');
    var summaryWorkshop = document.getElementById('summaryWorkshop');
    var summaryWsLbl    = document.getElementById('summaryWorkshopLabel');
    var summaryFeeRow   = document.getElementById('summaryFeeRow');
    var summaryFeeLbl   = document.getElementById('summaryFeeLabel');

    function computeFee() {
        var fee = 0;
        if (selectedCategory) fee += categoryPrices[selectedCategory] || 0;
        if (selectedWorkshop)  fee += workshopPrice;
        return fee;
    }

    function updateSummary() {
        if (!summaryCard) return;
        var hasSel = selectedCategory || selectedWorkshop;
        summaryCard.hidden = !hasSel;
        if (selectedCategory) {
            summaryCategory.hidden  = false;
            summaryCatLbl.textContent = categoryNames[selectedCategory] + ' ($' + (categoryPrices[selectedCategory] || 0) + ')';
        } else {
            summaryCategory.hidden = true;
        }
        if (selectedWorkshop) {
            summaryWorkshop.hidden  = false;
            summaryWsLbl.textContent = workshopNames[selectedWorkshop] + ' ($' + workshopPrice + ')';
        } else {
            summaryWorkshop.hidden = true;
        }
        if (hasSel) {
            summaryFeeRow.hidden  = false;
            summaryFeeLbl.textContent = '$' + computeFee();
        } else {
            summaryFeeRow.hidden = true;
        }
    }

    /* ── Category selection ── */
    function selectCategory(cat) {
        selectedCategory    = cat;
        catInput.value      = cat;
        document.querySelectorAll('.conf-registration-plan').forEach(function (btn) {
            var isSel = btn.dataset.category === cat;
            btn.classList.toggle('is-selected', isSel);
            btn.setAttribute('aria-checked', isSel ? 'true' : 'false');
            var lbl = btn.querySelector('.conf-registration-plan-select');
            if (lbl) lbl.innerHTML = isSel
                ? 'Selected <i class="ph ph-check" aria-hidden="true"></i>'
                : 'Select category <i class="ph ph-arrow-right" aria-hidden="true"></i>';
        });
        var isStudent = cat === 'student';
        if (studentDocument) studentDocument.hidden = !isStudent;
        if (studentId)       { studentId.required = isStudent; studentId.disabled = !isStudent; }
        updateSummary();
    }

    /* ── Workshop selection ── */
    function selectWorkshop(ws) {
        selectedWorkshop   = ws;
        workshopInput.value = ws;
        document.querySelectorAll('.conf-registration-workshop-card').forEach(function (btn) {
            var isSel = btn.dataset.workshop === ws;
            btn.classList.toggle('is-selected', isSel);
            btn.setAttribute('aria-checked', isSel ? 'true' : 'false');
            var lbl = btn.querySelector('.conf-registration-workshop-select');
            if (lbl) lbl.innerHTML = isSel
                ? 'Selected <i class="ph ph-check" aria-hidden="true"></i>'
                : 'Select workshop <i class="ph ph-arrow-right" aria-hidden="true"></i>';
        });
        updateSummary();
    }

    /* ── Bind category buttons ── */
    document.querySelectorAll('.conf-registration-plan').forEach(function (btn) {
        btn.addEventListener('click', function () { selectCategory(btn.dataset.category); });
        btn.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                e.preventDefault();
                var all  = Array.prototype.slice.call(document.querySelectorAll('.conf-registration-plan'));
                var next = all[(all.indexOf(btn) + (e.key === 'ArrowRight' ? 1 : all.length - 1)) % all.length];
                selectCategory(next.dataset.category);
                next.focus();
            }
        });
    });

    /* ── Bind workshop buttons ── */
    document.querySelectorAll('.conf-registration-workshop-card').forEach(function (btn) {
        btn.addEventListener('click', function () { selectWorkshop(btn.dataset.workshop); });
        btn.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                e.preventDefault();
                var all  = Array.prototype.slice.call(document.querySelectorAll('.conf-registration-workshop-card'));
                var next = all[(all.indexOf(btn) + (e.key === 'ArrowRight' ? 1 : all.length - 1)) % all.length];
                selectWorkshop(next.dataset.workshop);
                next.focus();
            }
        });
    });

    /* ── Professional category "Other" ── */
    if (profCatSelect) {
        profCatSelect.addEventListener('change', function () {
            var isOther = this.value === 'Other Healthcare Professional (Specify)';
            profCatOther.hidden    = !isOther;
            profCatOtherIn.required = isOther;
        });
    }

    /* ── Stepper ── */
    function showStep(step) {
        current = step;
        panels.forEach(function (panel) { panel.classList.toggle('active', panel.dataset.stepPanel == step); });
        indicators.forEach(function (item) {
            var v = Number(item.dataset.stepIndicator);
            item.classList.toggle('active', v === step);
            item.classList.toggle('done', v < step);
        });
        var card = form.closest('.conf-registration-form-card');
        if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (step === 3) {
            var nameVal = (document.getElementById('registrationFullName') || {}).value || '—';
            form.querySelector('[data-review="name"]').textContent  = nameVal;
            form.querySelector('[data-review="email"]').textContent = form.email.value;
            form.querySelector('[data-review="category"]').textContent = selectedCategory ? categoryNames[selectedCategory] : 'Not selected';
            form.querySelector('[data-review="workshop"]').textContent  = selectedWorkshop  ? workshopNames[selectedWorkshop]  : 'None';
            form.querySelector('[data-review="fee"]').textContent  = '$' + computeFee();
            form.querySelector('[data-review="payment"]').textContent = form.payment_screenshot.files.length ? form.payment_screenshot.files[0].name : 'Not attached';
        }
    }

    if (form) {
        form.querySelectorAll('[data-next-step]').forEach(function (button) {
            button.addEventListener('click', function () {
                var fields = form.querySelectorAll('[data-step-panel="' + current + '"] input:not([disabled]), [data-step-panel="' + current + '"] select');
                var valid  = true;
                fields.forEach(function (field) { if (!field.checkValidity()) { field.reportValidity(); valid = false; } });
                if (valid) showStep(Number(button.dataset.nextStep));
            });
        });
        form.querySelectorAll('[data-prev-step]').forEach(function (button) {
            button.addEventListener('click', function () { showStep(Number(button.dataset.prevStep)); });
        });
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (confirmDialog && typeof confirmDialog.showModal === 'function') confirmDialog.showModal();
            else if (window.confirm('Submit your registration to the conference team?')) form.submit();
        });
    }

    if (document.getElementById('registrationDialogBack')) {
        document.getElementById('registrationDialogBack').addEventListener('click', function () { confirmDialog.close(); });
    }
    if (dialogConfirm) {
        dialogConfirm.addEventListener('click', function () {
            dialogConfirm.disabled  = true;
            dialogConfirm.innerHTML = '<i class="ph ph-spinner-gap" aria-hidden="true"></i> Sending registration...';
            if (submitButton) submitButton.disabled = true;
            form.submit();
        });
    }
}());
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
