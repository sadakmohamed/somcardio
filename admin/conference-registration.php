<?php
require_once __DIR__ . '/../includes/admin_layout.php';

$db = getDB();
$conf = $db->query("SELECT * FROM conferences WHERE status = 'active' ORDER BY year DESC, id DESC LIMIT 1")->fetch();
$confId = $conf ? (int)$conf['id'] : 0;
$filter = $_GET['filter'] ?? 'all';

// ── Save registration settings ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header('Location: ' . SITE_URL . '/admin/conference-registration');
        exit;
    }
    if (!$confId) {
        setFlash('error', 'Please create an ongoing conference first.');
        header('Location: ' . SITE_URL . '/admin/conference-registration');
        exit;
    }
    $process = trim($_POST['process_text'] ?? '');
    $account = trim($_POST['account_number'] ?? '');
    $bank    = trim($_POST['bank_name'] ?? '');
    $holder  = trim($_POST['account_holder'] ?? '');

    $db->prepare(
        "INSERT INTO conference_registration_settings (conference_id, process_text, account_number, bank_name, account_holder)
         VALUES (:cid, :process, :account, :bank, :holder)
         ON DUPLICATE KEY UPDATE process_text = VALUES(process_text), account_number = VALUES(account_number),
         bank_name = VALUES(bank_name), account_holder = VALUES(account_holder)"
    )->execute([':cid' => $confId, ':process' => $process, ':account' => $account, ':bank' => $bank, ':holder' => $holder]);

    setFlash('success', 'Registration settings updated successfully.');
    header('Location: ' . SITE_URL . '/admin/conference-registration');
    exit;
}

// ── Approve / Decline via POST (with custom message) ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header('Location: ' . SITE_URL . '/admin/conference-registration');
        exit;
    }

    $id            = (int)($_POST['reg_id'] ?? 0);
    $status        = in_array($_POST['status'] ?? '', ['approved','declined']) ? $_POST['status'] : null;
    $customMessage = trim($_POST['custom_message'] ?? '');

    if (!$id || !$status) {
        setFlash('error', 'Invalid request.');
        header('Location: ' . SITE_URL . '/admin/conference-registration');
        exit;
    }

    $regStmt = $db->prepare("SELECT * FROM conference_registrations WHERE id = :id LIMIT 1");
    $regStmt->execute([':id' => $id]);
    $regItem = $regStmt->fetch();

    if ($regItem) {
        $db->prepare("UPDATE conference_registrations SET status = :status WHERE id = :id")
           ->execute([':status' => $status, ':id' => $id]);

        require_once __DIR__ . '/../config/mail.php';
        $clientEmail = $regItem['email'];
        $clientName  = trim(($regItem['first_name'] ?? '') . ' ' . ($regItem['last_name'] ?? ''));
        $confTitle   = trim((string)($conf['title'] ?? '')) ?: 'Somali Cardiac Society Annual Conference';
        $esc         = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

        if ($status === 'approved') {
            $subject = "Registration Approved — " . $confTitle;
            $msgBlock = $customMessage !== ''
                ? '<div style="background:#ecfdf5;border-left:4px solid #10b981;padding:16px 18px;border-radius:6px;margin:20px 0;">'
                  . '<p style="margin:0;font-size:14px;line-height:1.7;">' . $esc($customMessage) . '</p></div>'
                : '<div style="background:#ecfdf5;border-left:4px solid #10b981;padding:16px 18px;border-radius:6px;margin:20px 0;">'
                  . '<p style="margin:0 0 6px;font-size:14px;"><strong>Category:</strong> ' . $esc(ucfirst($regItem['category'])) . '</p>'
                  . '<p style="margin:0;font-size:14px;"><strong>Status:</strong> <span style="display:inline-block;padding:3px 9px;border-radius:12px;background:#10b981;color:#fff;font-weight:700;font-size:12px;">Confirmed Attendee</span></p>'
                  . '</div>';

            $htmlBody = '<!doctype html><html><body style="margin:0;padding:24px;background:#f2f6f8;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;color:#102a43;">'
                . '<div style="max-width:580px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e2ec;box-shadow:0 8px 24px rgba(0,0,0,0.06);">'
                . '<div style="background:#0D1B2A;padding:26px 28px;text-align:center;color:#fff;">'
                . '<h2 style="margin:0;font-size:20px;letter-spacing:-0.02em;">Somali Cardiac Society</h2>'
                . '<p style="margin:6px 0 0;color:#10b981;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;">Registration Approved ✓</p>'
                . '</div>'
                . '<div style="padding:28px;">'
                . '<p style="font-size:16px;line-height:1.6;margin-top:0;">Dear <strong>' . $esc($clientName) . '</strong>,</p>'
                . '<p style="font-size:14px;line-height:1.7;color:#486581;">Congratulations! Your registration and payment for the <strong>' . $esc($confTitle) . '</strong> have been verified and <strong>APPROVED</strong>.</p>'
                . $msgBlock
                . '<p style="font-size:14px;line-height:1.7;color:#486581;">We look forward to your participation. Please check our website for conference updates and programme schedules.</p>'
                . '<p style="font-size:13px;line-height:1.6;color:#627d98;margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px;">If you have any questions, feel free to reach out at <a href="mailto:conference@somcardio.so" style="color:#27AAE1;text-decoration:none;font-weight:600;">conference@somcardio.so</a>.</p>'
                . '</div></div></body></html>';

            try {
                sendSmtpMail($clientEmail, $subject, $htmlBody, 'conference@somcardio.so');
                setFlash('success', 'Registration approved and confirmation email sent to ' . $clientName . '.');
            } catch (Throwable $e) {
                error_log('Approval email failed: ' . $e->getMessage());
                setFlash('success', 'Registration approved (email could not be delivered).');
            }

        } elseif ($status === 'declined') {
            $subject = "Registration Update — " . $confTitle;
            $msgBlock = $customMessage !== ''
                ? '<div style="background:#fef2f2;border-left:4px solid #ED1C24;padding:16px 18px;border-radius:6px;margin:20px 0;">'
                  . '<p style="margin:0;font-size:14px;line-height:1.7;">' . $esc($customMessage) . '</p></div>'
                : '<p style="font-size:14px;line-height:1.7;color:#486581;">After reviewing your registration, we regret to inform you that your application could not be approved at this time. This is typically due to unverified payment or incomplete verification details.</p>';

            $htmlBody = '<!doctype html><html><body style="margin:0;padding:24px;background:#f2f6f8;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;color:#102a43;">'
                . '<div style="max-width:580px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e2ec;box-shadow:0 8px 24px rgba(0,0,0,0.06);">'
                . '<div style="background:#0D1B2A;padding:26px 28px;text-align:center;color:#fff;">'
                . '<h2 style="margin:0;font-size:20px;letter-spacing:-0.02em;">Somali Cardiac Society</h2>'
                . '<p style="margin:6px 0 0;color:#ED1C24;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;">Registration Status Update</p>'
                . '</div>'
                . '<div style="padding:28px;">'
                . '<p style="font-size:16px;line-height:1.6;margin-top:0;">Dear <strong>' . $esc($clientName) . '</strong>,</p>'
                . '<p style="font-size:14px;line-height:1.7;color:#486581;">Thank you for your interest in attending the <strong>' . $esc($confTitle) . '</strong>.</p>'
                . $msgBlock
                . '<p style="font-size:13px;line-height:1.6;color:#627d98;margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px;">If you have questions or would like to provide updated payment information, please contact us at <a href="mailto:conference@somcardio.so" style="color:#27AAE1;text-decoration:none;font-weight:600;">conference@somcardio.so</a>.</p>'
                . '</div></div></body></html>';

            try {
                sendSmtpMail($clientEmail, $subject, $htmlBody, 'conference@somcardio.so');
                setFlash('success', 'Registration declined and update email sent to ' . $clientName . '.');
            } catch (Throwable $e) {
                error_log('Decline email failed: ' . $e->getMessage());
                setFlash('success', 'Registration marked as declined.');
            }
        }
    }

    header('Location: ' . SITE_URL . '/admin/conference-registration?filter=' . $filter);
    exit;
}

// ── Fetch settings & registrations ──────────────────────────────────────────
$settings = null;
if ($confId) {
    $settingStmt = $db->prepare("SELECT * FROM conference_registration_settings WHERE conference_id = :cid LIMIT 1");
    $settingStmt->execute([':cid' => $confId]);
    $settings = $settingStmt->fetch();
}

$where = $confId ? "WHERE conference_id = :cid" : "WHERE 1=0";
if ($filter !== 'all') {
    $where .= " AND status = :filter";
}
$stmt = $db->prepare("SELECT * FROM conference_registrations $where ORDER BY registered_at DESC");
$params = [];
if ($confId) $params[':cid'] = $confId;
if ($filter !== 'all') $params[':filter'] = $filter;
$stmt->execute($params);
$registrations = $stmt->fetchAll();

// Counts for badges
$countStmt = $db->prepare("SELECT status, COUNT(*) as cnt FROM conference_registrations WHERE conference_id = :cid GROUP BY status");
$counts = ['pending' => 0, 'approved' => 0, 'declined' => 0, 'all' => 0];
if ($confId) {
    $countStmt->execute([':cid' => $confId]);
    foreach ($countStmt->fetchAll() as $row) {
        $counts[$row['status']] = (int)$row['cnt'];
        $counts['all'] += (int)$row['cnt'];
    }
}

startAdminLayout('Conference Registrations');
?>

<div class="page-title-block">
    <div>
        <h1><i class="ph ph-users" style="color:#27AAE1;margin-right:10px;"></i>Conference Registrations</h1>
        <p>Review registrations, manage payment settings, and send personalised approval or decline messages.</p>
    </div>
</div>

<!-- ── Payment Settings ─────────────────────────────────────────────────── -->
<div class="admin-card" style="margin-bottom:28px;">
    <div class="card-header"><h2>Registration Payment Settings</h2></div>
    <div class="card-body">
        <form method="POST">
            <?php echo csrfField(); ?>
            <input type="hidden" name="save_settings" value="1">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div class="form-group">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" value="<?php echo e($settings['bank_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="account_number" class="form-control" value="<?php echo e($settings['account_number'] ?? ''); ?>">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Account Holder</label>
                    <input type="text" name="account_holder" class="form-control" value="<?php echo e($settings['account_holder'] ?? ''); ?>">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Registration Process Text</label>
                    <textarea name="process_text" rows="5" class="form-control"><?php echo e($settings['process_text'] ?? 'Please pay the conference fee using the details above and upload the payment confirmation.'); ?></textarea>
                </div>
            </div>
            <div style="margin-top:18px;">
                <button type="submit" class="btn-admin btn-admin-primary">Save Registration Settings</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Registrations Table ──────────────────────────────────────────────── -->
<div class="admin-card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <h2>Registrations
            <?php if ($counts['pending'] > 0): ?>
                <span style="display:inline-block;background:#fef3c7;color:#92400e;font-size:12px;font-weight:700;padding:2px 9px;border-radius:10px;margin-left:6px;vertical-align:middle;"><?php echo $counts['pending']; ?> pending</span>
            <?php endif; ?>
        </h2>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php foreach (['all','pending','approved','declined'] as $f): ?>
                <a href="<?php echo SITE_URL; ?>/admin/conference-registration?filter=<?php echo $f; ?>"
                   class="btn-admin btn-admin-secondary btn-sm<?php echo $filter === $f ? ' btn-admin-primary' : ''; ?>">
                    <?php echo ucfirst($f); ?>
                    <?php if ($counts[$f] > 0): ?><span style="background:rgba(255,255,255,0.25);border-radius:8px;padding:1px 6px;margin-left:4px;"><?php echo $counts[$f]; ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($registrations)): ?>
            <table class="admin-table" id="dataTable">
                <thead>
                    <tr>
                        <th>Attendee</th>
                        <th>Email &amp; Phone</th>
                        <th>Category / Workshop</th>
                        <th>Payment Proof</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $reg):
                        $regName       = trim(($reg['first_name'] ?? '') . ' ' . ($reg['last_name'] ?? '')) ?: ($reg['full_name'] ?? '—');
                        $hasScreenshot = !empty($reg['payment_screenshot']);
                        $hasStudentId  = !empty($reg['student_id_path']);
                        $screenshotUrl = $hasScreenshot ? UPLOADS_URL . '/' . e($reg['payment_screenshot']) : '';
                        $studentIdUrl  = $hasStudentId  ? UPLOADS_URL . '/' . e($reg['student_id_path'])   : '';
                        $catLabels     = ['student' => 'Student / Trainee', 'professional' => 'Health Professional', 'ingo' => 'Institutional / Partner'];
                        $catLabel      = $catLabels[$reg['category']] ?? ucfirst($reg['category']);
                        $wsLabels      = [
                            'ecg_arrhythmia'           => 'ECG & Arrhythmia',
                            'cardiovascular_emergency' => 'CV Emergency',
                            'cardiac_surgery'          => 'Cardiac Surgery',
                        ];
                        $wsLabel = !empty($reg['workshop']) ? ($wsLabels[$reg['workshop']] ?? $reg['workshop']) : '';
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo e($regName); ?></strong>
                                <?php if (!empty($reg['organization'])): ?>
                                    <br><small style="color:#64748b;"><?php echo e($reg['organization']); ?> (<?php echo e($reg['country'] ?? ''); ?>)</small>
                                <?php endif; ?>
                                <?php if (!empty($reg['professional_category'])): ?>
                                    <br><small style="color:#0369a1;"><?php echo e($reg['professional_category']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span><?php echo e($reg['email']); ?></span>
                                <?php if (!empty($reg['phone'])): ?>
                                    <br><small style="color:#64748b;"><i class="ph ph-phone"></i> <?php echo e($reg['phone']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge" style="background:#e0f2fe;color:#0369a1;padding:3px 9px;border-radius:6px;font-weight:700;font-size:11px;"><?php echo e(strtoupper($reg['category'])); ?></span>
                                <?php if ($wsLabel): ?>
                                    <br><small style="color:#7c3aed;font-weight:600;"><?php echo e($wsLabel); ?> +$30</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($hasScreenshot): ?>
                                    <a href="<?php echo $screenshotUrl; ?>" target="_blank" class="btn-admin btn-admin-secondary btn-sm" style="display:inline-flex;align-items:center;gap:4px;" title="View payment receipt">
                                        <i class="ph ph-image"></i> Receipt
                                    </a>
                                <?php elseif (!empty($reg['payment_phone'])): ?>
                                    <small style="color:#0D1B2A;font-weight:600;"><i class="ph ph-device-mobile"></i> <?php echo e($reg['payment_phone']); ?></small>
                                <?php else: ?>
                                    <small style="color:#94a3b8;">None provided</small>
                                <?php endif; ?>
                                <?php if ($hasStudentId): ?>
                                    <a href="<?php echo $studentIdUrl; ?>" target="_blank" class="btn-admin btn-admin-secondary btn-sm" style="display:inline-flex;align-items:center;gap:4px;margin-top:4px;" title="View Student ID">
                                        <i class="ph ph-identification-card"></i> ID
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $stBg = ['approved' => '#ecfdf5', 'declined' => '#fef2f2', 'pending' => '#fef3c7'];
                                $stFg = ['approved' => '#10b981', 'declined' => '#ED1C24', 'pending' => '#b45309'];
                                $st   = $reg['status'];
                                ?>
                                <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:700;background:<?php echo $stBg[$st] ?? '#f1f5f9'; ?>;color:<?php echo $stFg[$st] ?? '#334155'; ?>;">
                                    <?php echo ucfirst($st); ?>
                                </span>
                            </td>
                            <td><small><?php echo e(date('M d, Y', strtotime($reg['registered_at']))); ?></small></td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:5px;">
                                    <!-- View details -->
                                    <button type="button" class="btn-admin btn-admin-secondary btn-sm view-reg-btn"
                                            data-id="<?php echo (int)$reg['id']; ?>"
                                            data-name="<?php echo e($regName); ?>"
                                            data-email="<?php echo e($reg['email']); ?>"
                                            data-phone="<?php echo e($reg['phone'] ?? 'N/A'); ?>"
                                            data-category="<?php echo e($catLabel); ?>"
                                            data-workshop="<?php echo e($wsLabel); ?>"
                                            data-profcat="<?php echo e($reg['professional_category'] ?? ''); ?>"
                                            data-org="<?php echo e($reg['organization'] ?? 'N/A'); ?>"
                                            data-country="<?php echo e($reg['country'] ?? 'N/A'); ?>"
                                            data-payphone="<?php echo e($reg['payment_phone'] ?? 'N/A'); ?>"
                                            data-receipt="<?php echo $screenshotUrl; ?>"
                                            data-studentid="<?php echo $studentIdUrl; ?>"
                                            data-status="<?php echo e($st); ?>">
                                        <i class="ph ph-eye"></i> View
                                    </button>
                                    <?php if ($st !== 'approved'): ?>
                                        <button type="button" class="btn-admin btn-admin-primary btn-sm action-approve-btn"
                                                data-id="<?php echo (int)$reg['id']; ?>"
                                                data-name="<?php echo e($regName); ?>"
                                                data-email="<?php echo e($reg['email']); ?>">
                                            <i class="ph ph-check-circle"></i> Approve
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($st !== 'declined'): ?>
                                        <button type="button" class="btn-admin btn-admin-danger btn-sm action-decline-btn"
                                                data-id="<?php echo (int)$reg['id']; ?>"
                                                data-name="<?php echo e($regName); ?>"
                                                data-email="<?php echo e($reg['email']); ?>">
                                            <i class="ph ph-x-circle"></i> Decline
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align:center;padding:48px 24px;color:#64748b;">
                <i class="ph ph-users" style="font-size:3rem;opacity:0.3;display:block;margin-bottom:12px;"></i>
                <p>No registrations found<?php echo $filter !== 'all' ? ' for filter: <strong>' . e($filter) . '</strong>' : ''; ?>.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Hidden POST form for approve/decline (with CSRF + custom message) ── -->
<form id="statusUpdateForm" method="POST" action="<?php echo SITE_URL; ?>/admin/conference-registration?filter=<?php echo urlencode($filter); ?>" style="display:none;">
    <?php echo csrfField(); ?>
    <input type="hidden" name="update_status" value="1">
    <input type="hidden" name="reg_id" id="formRegId" value="">
    <input type="hidden" name="status" id="formStatus" value="">
    <input type="hidden" name="custom_message" id="formCustomMessage" value="">
</form>

<script>
(function () {

    /* ── Shared helpers ── */
    function buildReceiptHtml(receipt, studentId) {
        let h = '';
        if (receipt) {
            h += `<div style="margin-top:12px;">
                    <p style="margin:0 0 5px;font-weight:700;font-size:13px;">Payment Screenshot:</p>
                    <a href="${receipt}" target="_blank">
                        <img src="${receipt}" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid #e2e8f0;object-fit:contain;" alt="Payment receipt">
                    </a><br>
                    <small><a href="${receipt}" target="_blank">Open full image</a></small>
                  </div>`;
        } else {
            h += `<p style="color:#64748b;font-size:13px;margin:6px 0;"><strong>Payment Screenshot:</strong> None uploaded</p>`;
        }
        if (studentId) {
            h += `<div style="margin-top:10px;">
                    <p style="margin:0 0 5px;font-weight:700;font-size:13px;">Student ID Proof:</p>
                    <a href="${studentId}" target="_blank">
                        <img src="${studentId}" style="max-width:100%;max-height:180px;border-radius:8px;border:1px solid #e2e8f0;object-fit:contain;" alt="Student ID">
                    </a><br>
                    <small><a href="${studentId}" target="_blank">Open full image</a></small>
                  </div>`;
        }
        return h;
    }

    function submitAction(regId, status, message) {
        document.getElementById('formRegId').value       = regId;
        document.getElementById('formStatus').value      = status;
        document.getElementById('formCustomMessage').value = message;
        document.getElementById('statusUpdateForm').submit();
    }

    /* ── View Registration ── */
    document.querySelectorAll('.view-reg-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const d = this.dataset;
            const content = `
                <div style="text-align:left;font-size:14px;line-height:1.7;">
                    <p><strong>Name:</strong> ${d.name}</p>
                    <p><strong>Email:</strong> ${d.email}</p>
                    <p><strong>Phone:</strong> ${d.phone}</p>
                    <p><strong>Category:</strong> <span style="font-weight:700;color:#0284c7;">${d.category}</span></p>
                    ${d.workshop ? `<p><strong>Workshop:</strong> ${d.workshop}</p>` : ''}
                    ${d.profcat  ? `<p><strong>Professional Category:</strong> ${d.profcat}</p>` : ''}
                    <p><strong>Organization:</strong> ${d.org} (${d.country})</p>
                    <p><strong>Payment Phone:</strong> ${d.payphone}</p>
                    <p><strong>Status:</strong> <strong>${d.status}</strong></p>
                    <hr style="border:0;border-top:1px solid #e2e8f0;margin:12px 0;">
                    ${buildReceiptHtml(d.receipt, d.studentid)}
                </div>`;

            Swal.fire({
                title: d.name,
                html: content,
                showCancelButton: false,
                confirmButtonText: 'Close',
                confirmButtonColor: '#0D1B2A',
                width: '620px'
            });
        });
    });

    /* ── Approve ── */
    document.querySelectorAll('.action-approve-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const regId = this.dataset.id;
            const name  = this.dataset.name;
            const email = this.dataset.email;

            Swal.fire({
                title: 'Approve Registration',
                html: `
                    <p style="text-align:left;margin-bottom:12px;color:#374151;font-size:14px;">
                        You are about to <strong>approve</strong> the registration for <strong>${name}</strong> (${email}).<br>
                        An approval email will be sent automatically.
                    </p>
                    <label style="display:block;text-align:left;font-weight:600;font-size:13px;margin-bottom:6px;color:#374151;">
                        Personalised approval message <span style="font-weight:400;color:#6b7280;">(optional)</span>
                    </label>
                    <textarea id="swalApproveMsg" rows="4"
                        placeholder="e.g. Welcome! We are delighted to confirm your place at the 2026 National Cardiac Conference. Please arrive at the venue by 8:00 AM on the first day."
                        style="width:100%;box-sizing:border-box;border:1.5px solid #d1d5db;border-radius:8px;padding:10px 12px;font-size:13px;line-height:1.6;resize:vertical;outline:none;"></textarea>
                    <p style="text-align:left;font-size:12px;color:#9ca3af;margin-top:6px;">
                        Leave blank to send the default approval confirmation.
                    </p>`,
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: '<i class="ph ph-check"></i> Approve &amp; Send Email',
                confirmButtonColor: '#10b981',
                cancelButtonText: 'Cancel',
                width: '600px',
                preConfirm: () => {
                    return document.getElementById('swalApproveMsg').value.trim();
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitAction(regId, 'approved', result.value || '');
                }
            });
        });
    });

    /* ── Decline ── */
    document.querySelectorAll('.action-decline-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const regId = this.dataset.id;
            const name  = this.dataset.name;
            const email = this.dataset.email;

            Swal.fire({
                title: 'Decline Registration',
                html: `
                    <p style="text-align:left;margin-bottom:12px;color:#374151;font-size:14px;">
                        You are about to <strong>decline</strong> the registration for <strong>${name}</strong> (${email}).<br>
                        A decline notification will be sent automatically.
                    </p>
                    <label style="display:block;text-align:left;font-weight:600;font-size:13px;margin-bottom:6px;color:#374151;">
                        Reason / personalised decline message <span style="font-weight:400;color:#6b7280;">(optional)</span>
                    </label>
                    <textarea id="swalDeclineMsg" rows="4"
                        placeholder="e.g. We were unable to verify the payment submitted. Please resend a clear screenshot of the transaction to conference@somcardio.so and reapply."
                        style="width:100%;box-sizing:border-box;border:1.5px solid #d1d5db;border-radius:8px;padding:10px 12px;font-size:13px;line-height:1.6;resize:vertical;outline:none;"></textarea>
                    <p style="text-align:left;font-size:12px;color:#9ca3af;margin-top:6px;">
                        Leave blank to send the default decline message.
                    </p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="ph ph-x"></i> Decline &amp; Send Email',
                confirmButtonColor: '#ED1C24',
                cancelButtonText: 'Cancel',
                width: '600px',
                preConfirm: () => {
                    return document.getElementById('swalDeclineMsg').value.trim();
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitAction(regId, 'declined', result.value || '');
                }
            });
        });
    });

}());
</script>

<?php endAdminLayout(); ?>
