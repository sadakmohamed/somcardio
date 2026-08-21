<?php
/**
 * Contact Us Page — Somali Cardiac Society
 * Sends email to sadikothm@gmail.com via PHP mail()
 */
require_once __DIR__ . '/config/auth.php';

$pageTitle       = 'Contact Us';
$pageDescription = 'Get in touch with the Somali Cardiac Society for inquiries, support, or membership questions.';

// ─── AJAX/JSON handler ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');

    // CSRF
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.']);
        exit;
    }

    // Sanitize inputs
    $name    = trim(filter_input(INPUT_POST, 'name',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $email   = filter_input(INPUT_POST, 'email',   FILTER_VALIDATE_EMAIL);
    $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

    if (!$name || !$email || !$subject || !$message) {
        echo json_encode(['success' => false, 'message' => 'Please fill out all fields with valid information.']);
        exit;
    }

    // Compose HTML email
    $to      = 'sadikothm@gmail.com';
    $mailSubject = 'SCS Contact Form: ' . $subject;

    $htmlBody = "
    <html><body style='font-family:Inter,Arial,sans-serif;background:#f4f7fa;padding:0;margin:0;'>
    <div style='max-width:580px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);'>
        <div style='background:linear-gradient(135deg,#001B2E,#003060);padding:32px;text-align:center;'>
            <h2 style='color:#fff;margin:0;font-size:1.3rem;'>New Contact Form Message</h2>
            <p style='color:rgba(255,255,255,0.6);margin:8px 0 0;font-size:0.85rem;'>Somali Cardiac Society Website</p>
        </div>
        <div style='padding:32px;'>
            <table style='width:100%;border-collapse:collapse;'>
                <tr><td style='padding:10px 0;border-bottom:1px solid #E2E8F0;color:#8492A6;font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;width:110px;'>Name</td>
                    <td style='padding:10px 0;border-bottom:1px solid #E2E8F0;color:#0D1B2A;font-size:0.95rem;font-weight:600;'>" . htmlspecialchars($name) . "</td></tr>
                <tr><td style='padding:10px 0;border-bottom:1px solid #E2E8F0;color:#8492A6;font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;'>Email</td>
                    <td style='padding:10px 0;border-bottom:1px solid #E2E8F0;color:#27AAE1;font-size:0.95rem;'><a href='mailto:" . htmlspecialchars($email) . "' style='color:#27AAE1;'>" . htmlspecialchars($email) . "</a></td></tr>
                <tr><td style='padding:10px 0;border-bottom:1px solid #E2E8F0;color:#8492A6;font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;'>Subject</td>
                    <td style='padding:10px 0;border-bottom:1px solid #E2E8F0;color:#0D1B2A;font-size:0.95rem;'>" . htmlspecialchars($subject) . "</td></tr>
            </table>
            <div style='margin-top:24px;'>
                <p style='color:#8492A6;font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:12px;'>Message</p>
                <div style='background:#F4F7FA;border-radius:8px;padding:20px;color:#4A5568;font-size:0.95rem;line-height:1.7;white-space:pre-wrap;'>" . nl2br(htmlspecialchars($message)) . "</div>
            </div>
        </div>
        <div style='background:#F4F7FA;padding:20px 32px;text-align:center;border-top:1px solid #E2E8F0;'>
            <p style='color:#8492A6;font-size:0.78rem;margin:0;'>This message was sent from the <strong>SCS Contact Form</strong> at somalicardiac.org</p>
        </div>
    </div>
    </body></html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: SCS Website <no-reply@somalicardiac.org>\r\n";
    $headers .= "Reply-To: " . htmlspecialchars($name) . " <" . $email . ">\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $sent = @mail($to, $mailSubject, $htmlBody, $headers);

    if ($sent) {
        echo json_encode(['success' => true,  'message' => 'Your message has been sent successfully! We will get back to you shortly.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send your message. Please try calling us directly or emailing info@somalicardiac.org']);
    }
    exit;
}

// ─── Normal page render ───────────────────────────────────────────────────────
include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>Contact Us</h1>
        <p>Get in touch with the Somali Cardiac Society</p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <span style="color:rgba(255,255,255,0.7);">Contact Us</span>
        </div>
    </div>
</div>

<!-- Contact Grid -->
<section class="section">
    <div class="container">
        <div class="contact-grid">

            <!-- Contact Form -->
            <div class="contact-form fade-in">
                <h3>Send Us a Message</h3>
                <p style="color:var(--text-secondary);margin-bottom:28px;font-size:0.9rem;margin-top:6px;">
                    Have questions about our society, membership, or events? Drop us a line and we'll respond promptly.
                </p>

                <form id="contactForm" autocomplete="off">
                    <?php echo csrfField(); ?>

                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required placeholder="Enter your full name">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required placeholder="Enter your email address">
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <input type="text" id="subject" name="subject" required placeholder="What is this inquiry about?">
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" required placeholder="Write your message here..."></textarea>
                    </div>

                    <button type="submit" id="sendBtn" class="btn btn-primary" style="width:100%;padding:15px;">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Info + Map -->
            <div style="display:flex;flex-direction:column;gap:28px;">
                <div class="contact-info-card fade-in">
                    <h3>Contact Information</h3>

                    <div class="contact-item">
                        <div class="icon">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                        </div>
                        <div>
                            <h4>Phone Number</h4>
                            <p>+252 61 0188866</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="icon">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                        </div>
                        <div>
                            <h4>Email Address</h4>
                            <p>info@somalicardiac.org</p>
                            <p style="margin-top:4px;">support@somalicardiac.org</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="icon">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                        </div>
                        <div>
                            <h4>Location</h4>
                            <p>Hodan District, Mogadishu, Somalia</p>
                        </div>
                    </div>
                </div>

                <!-- Map embed -->
                <div class="card fade-in" style="overflow:hidden;border-radius:var(--radius-lg);">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126912.60040003827!2d45.24505994!3d2.0469343!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3d58422b0f2de7d3%3A0xc80e00c47a99e78!2sHodan%2C%20Mogadishu%2C%20Somalia!5e0!3m2!1sen!2s!4v1691000000000!5m2!1sen!2s"
                        width="100%" height="260" style="border:0;display:block;"
                        allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="SCS Office Location">
                    </iframe>
                </div>
            </div>

        </div><!-- /.contact-grid -->
    </div>
</section>

<!-- SweetAlert form handler -->
<script>
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('sendBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24" style="animation:spin 1s linear infinite;flex-shrink:0;"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 14.03 20 13.07 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg> Sending...';

    const formData = new FormData(this);

    try {
        const res  = await fetch('contact.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Message Sent!',
                text: data.message,
                confirmButtonColor: '#27AAE1',
                confirmButtonText: 'Great, thanks!',
                iconColor: '#27AAE1',
                customClass: { popup: 'swal-custom' }
            });
            document.getElementById('contactForm').reset();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Sending Failed',
                text: data.message,
                confirmButtonColor: '#ED1C24',
                confirmButtonText: 'Try Again',
                iconColor: '#ED1C24'
            });
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Something went wrong. Please check your connection and try again.',
            confirmButtonColor: '#ED1C24'
        });
    }

    btn.disabled = false;
    btn.innerHTML = '<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg> Send Message';
});
</script>

<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.swal-custom { border-radius: 18px !important; }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
