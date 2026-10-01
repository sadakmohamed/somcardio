<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/conference/call-for-abstracts');
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid security token. Please try again.');
    header('Location: ' . SITE_URL . '/conference/call-for-abstracts');
    exit;
}

$db = getDB();
$conf = $db->query("SELECT * FROM conferences WHERE status = 'active' ORDER BY id DESC LIMIT 1")->fetch();
if (!$conf) {
    setFlash('error', 'Abstract submissions are not available right now.');
    header('Location: ' . SITE_URL . '/conference/call-for-abstracts');
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$organization = trim($_POST['organization'] ?? '');
$title = trim($_POST['title'] ?? '');
$summary = trim($_POST['summary'] ?? '');
$subthemeId = filter_input(INPUT_POST, 'subtheme_id', FILTER_VALIDATE_INT) ?: null;
$subthemeTitle = '';

if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $title === '' || $summary === '') {
    setFlash('error', 'Please fill in all required abstract fields.');
    header('Location: ' . SITE_URL . '/conference/call-for-abstracts');
    exit;
}

$availableThemes = $db->prepare("SELECT COUNT(*) FROM conference_subthemes WHERE conference_id = :cid AND is_active = 1");
$availableThemes->execute([':cid' => $conf['id']]);
if ((int)$availableThemes->fetchColumn() > 0 && !$subthemeId) {
    setFlash('error', 'Please select a scientific subtheme for your abstract.');
    header('Location: ' . SITE_URL . '/conference/call-for-abstracts#abstract-submission');
    exit;
}

if ($subthemeId) {
    $themeStmt = $db->prepare("SELECT title FROM conference_subthemes WHERE id = :id AND conference_id = :cid AND is_active = 1 LIMIT 1");
    $themeStmt->execute([':id' => $subthemeId, ':cid' => $conf['id']]);
    $subthemeTitle = (string)($themeStmt->fetchColumn() ?: '');
    if ($subthemeTitle === '') {
        setFlash('error', 'That scientific subtheme is no longer available. Please select another theme.');
        header('Location: ' . SITE_URL . '/conference/call-for-abstracts#abstract-submission');
        exit;
    }
}

$abstractFile = null;
if (isset($_FILES['abstract_file']) && $_FILES['abstract_file']['error'] === UPLOAD_ERR_OK) {
    $uploaded = handleImageUpload($_FILES['abstract_file'], 'abstracts');
    if ($uploaded) {
        $abstractFile = $uploaded;
    }
}

if ($abstractFile === null) {
    setFlash('error', 'Please upload a valid PDF abstract.');
    header('Location: ' . SITE_URL . '/conference/call-for-abstracts');
    exit;
}

$stmt = $db->prepare("INSERT INTO conference_abstracts (conference_id, subtheme_id, subtheme_title, full_name, email, organization, title, summary, file_path, status) VALUES (:cid, :subtheme_id, :subtheme_title, :full_name, :email, :organization, :title, :summary, :file_path, 'pending')");
try {
    $stmt->execute([
        ':cid' => $conf['id'],
        ':subtheme_id' => $subthemeId,
        ':subtheme_title' => $subthemeTitle !== '' ? $subthemeTitle : null,
        ':full_name' => $fullName,
        ':email' => $email,
        ':organization' => $organization,
        ':title' => $title,
        ':summary' => $summary,
        ':file_path' => $abstractFile,
    ]);
} catch (Exception $e) {
    setFlash('error', 'Unable to save the abstract. Please try again.');
    header('Location: ' . SITE_URL . '/conference/call-for-abstracts');
    exit;
}

$fileUrl = UPLOADS_URL . '/' . implode('/', array_map('rawurlencode', explode('/', $abstractFile)));
$mailSubject = 'New conference abstract submission: ' . $title;
$htmlBody = '<!doctype html><html><body style="margin:0;background:#f2f6f8;font-family:Arial,sans-serif;color:#17384b;">'
    . '<div style="max-width:680px;margin:28px auto;background:#fff;border:1px solid #dbe6e8;border-radius:12px;overflow:hidden;">'
    . '<div style="padding:24px 28px;background:#123b50;color:#fff;"><p style="margin:0 0 8px;color:#8de2d5;font-size:12px;font-weight:bold;letter-spacing:1.4px;">SOMALI CARDIAC SOCIETY</p><h1 style="margin:0;font-size:22px;">New abstract submission</h1></div>'
    . '<div style="padding:26px 28px;">'
    . '<table role="presentation" style="width:100%;border-collapse:collapse;">'
    . '<tr><td style="padding:10px 0;border-bottom:1px solid #e7edef;color:#738792;width:145px;">Conference</td><td style="padding:10px 0;border-bottom:1px solid #e7edef;font-weight:bold;">' . e($conf['title']) . ' (' . e((string)$conf['year']) . ')</td></tr>'
    . '<tr><td style="padding:10px 0;border-bottom:1px solid #e7edef;color:#738792;">Author</td><td style="padding:10px 0;border-bottom:1px solid #e7edef;font-weight:bold;">' . e($fullName) . '</td></tr>'
    . '<tr><td style="padding:10px 0;border-bottom:1px solid #e7edef;color:#738792;">Email</td><td style="padding:10px 0;border-bottom:1px solid #e7edef;"><a href="mailto:' . e($email) . '" style="color:#087f8b;">' . e($email) . '</a></td></tr>'
    . '<tr><td style="padding:10px 0;border-bottom:1px solid #e7edef;color:#738792;">Organization</td><td style="padding:10px 0;border-bottom:1px solid #e7edef;">' . e($organization !== '' ? $organization : 'Not provided') . '</td></tr>'
    . '<tr><td style="padding:10px 0;border-bottom:1px solid #e7edef;color:#738792;">Scientific subtheme</td><td style="padding:10px 0;border-bottom:1px solid #e7edef;">' . e($subthemeTitle !== '' ? $subthemeTitle : 'Not selected') . '</td></tr>'
    . '<tr><td style="padding:10px 0;border-bottom:1px solid #e7edef;color:#738792;">Abstract title</td><td style="padding:10px 0;border-bottom:1px solid #e7edef;font-weight:bold;">' . e($title) . '</td></tr>'
    . '</table>'
    . '<h2 style="margin:24px 0 8px;font-size:15px;">Abstract summary</h2><div style="padding:15px;border-radius:8px;background:#f4f8f8;color:#425d6b;line-height:1.7;white-space:pre-wrap;">' . nl2br(e($summary)) . '</div>'
    . '<p style="margin:22px 0 0;"><a href="' . e($fileUrl) . '" style="display:inline-block;padding:12px 16px;border-radius:7px;background:#138a93;color:#fff;text-decoration:none;font-weight:bold;">Open uploaded PDF</a></p>'
    . '<p style="margin:20px 0 0;color:#82939b;font-size:12px;">Submission ID: ' . (int)$db->lastInsertId() . '</p>'
    . '</div></div></body></html>';

try {
    sendSmtpMail('coference@somcardio.so', $mailSubject, $htmlBody, $email);
    setFlash('success', 'Your abstract was saved and emailed to the conference team. The scientific committee will review it soon.');
} catch (Throwable $e) {
    error_log('Abstract submission email failed: ' . $e->getMessage());
    if (str_contains($e->getMessage(), 'SMTP credentials are missing or still set to placeholders')) {
        setFlash('error', 'Your abstract was saved, but email delivery is not configured on this website yet. The administrator needs to configure the SMTP email settings. Please contact coference@somcardio.so and include your abstract title.');
    } else {
        setFlash('error', 'Your abstract was saved, but the email could not be delivered to the conference team. Please contact coference@somcardio.so and include your abstract title.');
    }
}

header('Location: ' . SITE_URL . '/conference/call-for-abstracts');
exit;
