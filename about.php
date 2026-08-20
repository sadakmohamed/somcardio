<?php
/**
 * About Page — Somali Cardiac Society
 */
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'About Us';
$pageDescription = 'Learn about the Somali Society of Cardiology — our mission, vision, objectives, and cardiac services across Somalia.';

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>About Us</h1>
        <p>Advancing cardiovascular health care across Somalia since 2023</p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <span style="color:rgba(255,255,255,0.8);">About</span>
        </div>
    </div>
</div>

<!-- Organization Profile -->
<section class="section">
    <div class="container">
        <div class="about-intro fade-in">
            <div class="about-text">
                <h3>Somali Society of Cardiology</h3>
                <p>The Somali Society of Cardiology (SSC) was founded in <strong>2023</strong> with the objective of uniting healthcare professionals who share a common interest in cardiovascular diseases (CVDs).</p>
                <p>Our society comprises a diverse membership that includes physicians, medical officers, nurses, cardiologists, and other healthcare providers committed to advancing cardiovascular health in Somalia.</p>
                <p>Our primary goal is to uphold and elevate the highest standards of cardiovascular care within the country. We achieve this through a robust commitment to research, education, and the promotion of best practices in cardiovascular medicine.</p>
                <p>In addition, we actively engage in advocacy efforts aimed at improving the accessibility and affordability of high-quality cardiovascular services across Somalia.</p>
            </div>
            <div style="display:flex;flex-direction:column;gap:20px;">
                <div style="background:var(--primary-blue-light);border-radius:var(--radius-md);padding:40px;text-align:center;">
                    <img src="<?php echo SITE_URL; ?>/images/logo.png" alt="SCS Logo" style="max-width:200px;margin:0 auto;">
                    <p style="margin-top:20px;font-weight:600;color:var(--primary-blue);font-size:1.1rem;">Established 2023</p>
                    <p style="color:var(--text-secondary);font-size:0.9rem;">Mogadishu, Somalia</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="section section-gray">
    <div class="container">
        <div class="section-header fade-in">
            <h2>Our Mission & Vision</h2>
            <p>Guided by purpose, driven by excellence</p>
            <div class="section-line"></div>
        </div>
        <div class="values-grid">
            <div class="value-card fade-in">
                <div class="icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                </div>
                <h4>Our Mission</h4>
                <p>To promote and uphold the highest standards of cardiovascular care by advocating for best practices, providing comprehensive public and professional education, preventing cardiovascular diseases, and supporting and coordinating research initiatives in collaboration with key stakeholders.</p>
            </div>
            <div class="value-card fade-in">
                <div class="icon" style="background:var(--primary-red-light);color:var(--primary-red);">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                </div>
                <h4>Our Vision</h4>
                <p>To be a leading authority in cardiovascular care, setting exemplary standards and providing the region with unparalleled expertise in the prevention, diagnosis, and treatment of cardiovascular diseases.</p>
            </div>
        </div>
    </div>
</section>

<!-- Aims & Objectives -->
<section class="section">
    <div class="container">
        <div class="section-header fade-in">
            <h2>Aims & Objectives</h2>
            <p>Our core commitments to advancing cardiac healthcare</p>
            <div class="section-line"></div>
        </div>
        <div class="values-grid">
            <div class="value-card fade-in">
                <div class="icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg>
                </div>
                <h4>Advancing Cardiology Science</h4>
                <p>To advance the science and art of cardiology in Somalia, and to achieve international standards of best practice.</p>
            </div>
            <div class="value-card fade-in">
                <div class="icon" style="background:var(--primary-red-light);color:var(--primary-red);">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <h4>Knowledge Exchange</h4>
                <p>To promote exchange of scientific knowledge among cardiology professionals through meetings, conferences, and collaborative programs.</p>
            </div>
            <div class="value-card fade-in">
                <div class="icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                </div>
                <h4>Research & Scholarships</h4>
                <p>To encourage, support, and carry out research in cardiology and to award research scholarships for advancing cardiac knowledge.</p>
            </div>
            <div class="value-card fade-in">
                <div class="icon" style="background:var(--primary-red-light);color:var(--primary-red);">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                </div>
                <h4>International Collaboration</h4>
                <p>To foster relationships with international and regional societies of cardiology, including African, European, and American cardiac organizations.</p>
            </div>
        </div>
    </div>
</section>

<!-- Cardiac Services -->
<section class="section section-gray">
    <div class="container">
        <div class="section-header fade-in">
            <h2>Cardiac Services</h2>
            <p>Comprehensive cardiovascular healthcare across Somalia</p>
            <div class="section-line"></div>
        </div>
        <div class="services-grid">
            <div class="service-card fade-in">
                <h4>Cardiac Facilities</h4>
                <p>Our cardiac facilities across Somalia are equipped with modern technology and staffed by skilled cardiologists dedicated to providing advanced cardiovascular care, including non-invasive testing, catheterization labs, and surgical procedures.</p>
            </div>
            <div class="service-card fade-in">
                <h4>Medical Services</h4>
                <p>We provide a comprehensive suite of services including preventive screenings for hypertension and cholesterol, as well as advanced treatments like angioplasty, stent placement, and heart surgery with personalized treatment plans.</p>
            </div>
            <div class="service-card fade-in">
                <h4>Clinics & Hospitals Directory</h4>
                <p>A comprehensive directory helping patients find medical centers offering dedicated cardiovascular services throughout Somalia, ensuring timely and appropriate care for both routine check-ups and advanced treatments.</p>
            </div>
            <div class="service-card fade-in">
                <h4>Emergency Cardiac Care</h4>
                <p>Our emergency cardiac care services provide immediate, life-saving treatment through specialized response teams and ambulance services, equipped to stabilize patients before hospital arrival and improve survival rates.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="quick-contact">
    <div class="container fade-in">
        <h3>Join the Somali Cardiac Society</h3>
        <p>Connect with fellow cardiac professionals and contribute to advancing cardiovascular healthcare in Somalia.</p>
        <a href="contact.php" class="btn btn-lg" style="background:white;color:var(--primary-blue);font-weight:700;">Get In Touch</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
