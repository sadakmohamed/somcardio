<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/conference/registration');
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid security token. Please try again.');
    header('Location: ' . SITE_URL . '/conference/registration');
    exit;
}

$db = getDB();
$conf = $db->query("SELECT * FROM conferences WHERE status = 'active' ORDER BY id DESC LIMIT 1")->fetch();
if (!$conf) {
    setFlash('error', 'Conference registration is not available right now.');
    header('Location: ' . SITE_URL . '/conference/registration');
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');

if ($fullName !== '' && $firstName === '') {
    $parts = explode(' ', $fullName, 2);
    $firstName = $parts[0];
    $lastName = $parts[1] ?? '.';
}

$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$organization = trim($_POST['organization'] ?? '');
$country = trim($_POST['country'] ?? '');
$category = in_array($_POST['category'] ?? '', ['student','professional','ingo']) ? $_POST['category'] : 'professional';
$paymentPhone = trim($_POST['payment_phone'] ?? '');
$workshop = trim($_POST['workshop'] ?? '');
$profCategory = trim($_POST['professional_category'] ?? '');
if ($profCategory === 'Other Healthcare Professional (Specify)' && !empty($_POST['professional_category_other'])) {
    $profCategory = 'Other: ' . trim($_POST['professional_category_other']);
}

if ($firstName === '' || $email === '' || $phone === '' || $country === '') {
    setFlash('error', 'Please complete all required fields.');
    header('Location: ' . SITE_URL . '/conference/registration');
    exit;
}

$studentPath = null;
if (($category === 'student' || in_array($profCategory, ['Medical Student', 'Intern'])) && isset($_FILES['student_id_path']) && $_FILES['student_id_path']['error'] === UPLOAD_ERR_OK) {
    $uploaded = handleImageUpload($_FILES['student_id_path'], 'registrations');
    if ($uploaded) {
        $studentPath = $uploaded;
    }
}

$paymentPath = null;
if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK) {
    $uploaded = handleImageUpload($_FILES['payment_screenshot'], 'registrations');
    if ($uploaded) {
        $paymentPath = $uploaded;
    }
}

$stmt = $db->prepare("INSERT INTO conference_registrations (conference_id, category, professional_category, workshop, first_name, last_name, email, phone, organization, country, student_id_path, payment_screenshot, payment_phone, status) VALUES (:cid, :category, :professional_category, :workshop, :first_name, :last_name, :email, :phone, :organization, :country, :student_id_path, :payment_screenshot, :payment_phone, 'pending')");
$stmt->execute([
    ':cid' => $conf['id'],
    ':category' => $category,
    ':professional_category' => $profCategory !== '' ? $profCategory : null,
    ':workshop' => $workshop !== '' ? $workshop : null,
    ':first_name' => $firstName,
    ':last_name' => $lastName,
    ':email' => $email,
    ':phone' => $phone,
    ':organization' => $organization,
    ':country' => $country,
    ':student_id_path' => $studentPath,
    ':payment_screenshot' => $paymentPath,
    ':payment_phone' => $paymentPhone,
]);

// Send email notification to client (waiting for approval)
require_once __DIR__ . '/../config/mail.php';

$categoryPrices = ['student' => '$30', 'professional' => '$60', 'ingo' => '$90'];
$categoryLabels = ['student' => 'STUDENT / TRAINEE ($30)', 'professional' => 'HEALTH PROFESSIONAL ($60)', 'ingo' => 'INSTITUTIONAL / PARTNER DELEGATE ($90)'];
$catLabel = !empty($workshop) ? ($workshop . ' ($30)') : ($categoryLabels[$category] ?? ucfirst($category));
$confTitle = trim((string)($conf['title'] ?? '')) ?: '2nd National Cardiac Conference 2026';

$displayName = trim($firstName . ' ' . $lastName);
$clientSubject = "Registration Received — " . $confTitle;
$clientHtml = '<!doctype html><html><body style="margin:0;padding:24px;background:#f2f6f8;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;color:#102a43;">'
    . '<div style="max-width:580px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e2ec;box-shadow:0 8px 24px rgba(0,0,0,0.06);">'
    . '<div style="background:#0D1B2A;padding:26px 28px;text-align:center;color:#fff;">'
    . '<h2 style="margin:0;font-size:20px;letter-spacing:-0.02em;">Somali Cardiac Society</h2>'
    . '<p style="margin:6px 0 0;color:#27AAE1;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;">Conference Registration Received</p>'
    . '</div>'
    . '<div style="padding:28px;">'
    . '<p style="font-size:16px;line-height:1.6;margin-top:0;">Dear <strong>' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . '</strong>,</p>'
    . '<p style="font-size:14px;line-height:1.7;color:#486581;">Thank you for registering for the <strong>' . htmlspecialchars($confTitle, ENT_QUOTES, 'UTF-8') . '</strong>. We have received your registration details and payment information.</p>'
    . '<div style="background:#f0f8ff;border-left:4px solid #27AAE1;padding:16px 18px;border-radius:6px;margin:20px 0;">'
    . '<p style="margin:0 0 6px;font-size:14px;"><strong>Selected Registration:</strong> ' . htmlspecialchars($catLabel, ENT_QUOTES, 'UTF-8') . '</p>'
    . (!empty($profCategory) ? '<p style="margin:0 0 6px;font-size:14px;"><strong>Professional Category:</strong> ' . htmlspecialchars($profCategory, ENT_QUOTES, 'UTF-8') . '</p>' : '')
    . '<p style="margin:0;font-size:14px;"><strong>Status:</strong> <span style="display:inline-block;padding:3px 9px;border-radius:12px;background:#fef3c7;color:#92400e;font-weight:700;font-size:12px;">Waiting for Admin Approval</span></p>'
    . '</div>'
    . '<p style="font-size:14px;line-height:1.7;color:#486581;">Our organizing committee is reviewing your submission and verifying the payment details. Once approved, you will receive an official confirmation email.</p>'
    . '<p style="font-size:13px;line-height:1.6;color:#627d98;margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px;">If you need assistance or have questions regarding your registration, please contact us at <a href="mailto:conference@somcardio.so" style="color:#27AAE1;text-decoration:none;font-weight:600;">conference@somcardio.so</a>.</p>'
    . '</div>'
    . '</div></body></html>';

try {
    sendSmtpMail($email, $clientSubject, $clientHtml, 'conference@somcardio.so');
} catch (Throwable $e) {
    error_log('Registration client email failed: ' . $e->getMessage());
}

// Send notification to conference team
$teamSubject = "New Conference Registration: " . $displayName . " (" . $catLabel . ")";
$teamHtml = '<!doctype html><html><body style="font-family:sans-serif;padding:20px;color:#102a43;">'
    . '<h3>New Conference Registration Received</h3>'
    . '<p><strong>Name:</strong> ' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p><strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p><strong>Phone:</strong> ' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p><strong>Selected:</strong> ' . htmlspecialchars($catLabel, ENT_QUOTES, 'UTF-8') . '</p>'
    . (!empty($profCategory) ? '<p><strong>Professional Category:</strong> ' . htmlspecialchars($profCategory, ENT_QUOTES, 'UTF-8') . '</p>' : '')
    . '<p><strong>Country:</strong> ' . htmlspecialchars($country, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p><strong>Organization:</strong> ' . htmlspecialchars($organization, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p><strong>Payment Phone:</strong> ' . htmlspecialchars($paymentPhone, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p>Please log in to the admin panel to review payment verification and approve or decline this registration.</p>'
    . '</body></html>';

try {
    sendSmtpMail('conference@somcardio.so', $teamSubject, $teamHtml, $email);
} catch (Throwable $e) {
    error_log('Registration committee notification failed: ' . $e->getMessage());
}

setFlash('success', 'Your registration has been submitted successfully! We have sent a confirmation email. Your registration is currently awaiting admin verification.');
header('Location: ' . SITE_URL . '/conference/registration');
exit;
