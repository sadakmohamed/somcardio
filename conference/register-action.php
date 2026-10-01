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

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$organization = trim($_POST['organization'] ?? '');
$country = trim($_POST['country'] ?? '');
$category = in_array($_POST['category'] ?? '', ['student','professional','ingo']) ? $_POST['category'] : 'professional';
$paymentPhone = trim($_POST['payment_phone'] ?? '');

if ($firstName === '' || $lastName === '' || $email === '' || $phone === '' || $country === '') {
    setFlash('error', 'Please complete all required fields.');
    header('Location: ' . SITE_URL . '/conference/registration');
    exit;
}

$studentPath = null;
if (($category === 'student') && isset($_FILES['student_id_path']) && $_FILES['student_id_path']['error'] === UPLOAD_ERR_OK) {
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

$stmt = $db->prepare("INSERT INTO conference_registrations (conference_id, category, first_name, last_name, email, phone, organization, country, student_id_path, payment_screenshot, payment_phone, status) VALUES (:cid, :category, :first_name, :last_name, :email, :phone, :organization, :country, :student_id_path, :payment_screenshot, :payment_phone, 'pending')");
$stmt->execute([
    ':cid' => $conf['id'],
    ':category' => $category,
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

setFlash('success', 'Your registration has been submitted successfully. We will contact you shortly.');
header('Location: ' . SITE_URL . '/conference/registration');
exit;
