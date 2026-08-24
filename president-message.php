<?php
/**
 * President's Message — Somali Cardiac Society
 */
require_once __DIR__ . '/config/auth.php';

$pageTitle = "President's Message";
$pageDescription = 'Message from the President of the Somali Cardiac Society — Dr. Abdullahi Mohamed Hassan (Fujeyra)';

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header" style="background: linear-gradient(135deg, rgba(0,27,46,0.9), rgba(0,40,69,0.68)), url('<?php echo SITE_URL; ?>/images/about1.png') center/cover no-repeat;">
    <div class="container">
        <h1>President's Message</h1>
        <p>Leadership note from the President of the Somali Cardiac Society (SCS)</p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <a href="about.php">About Us</a>
            <span>›</span>
            <span style="color:rgba(255,255,255,0.8);">President's Message</span>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="president-message-card">
            
            <div class="president-card-grid">
                
                <!-- Left Column: President Profile Card -->
                <div class="president-profile-column">
                    <div class="president-profile-heading"><span></span><strong></strong></div>
                    <div class="president-portrait">
                        <img src="<?php echo SITE_URL; ?>/images/president.jpeg" alt="Dr. Abdullahi Mohamed Hassan (Fujeyra)">
                    </div>
                    <h3>Dr. Abdullahi Mohamed Hassan</h3>
                    <p class="president-name-note">(Fujeyra)</p>
                    <div class="president-role">
                        President, Somali Cardiac Society
                    </div>
                    <div class="president-profile-footer">Together for better cardiovascular care</div>
                </div>

                <!-- Right Column: Official Message Text -->
                <div class="president-message-content">
                    <div>
                        <div class="president-message-heading">
                            <div class="president-quote-icon">
                                <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/></svg>
                            </div>
                            <div>
                                <h2>President’s Message</h2>
                                <p>Official address to SCS members, colleagues, and international partners</p>
                            </div>
                        </div>

                        <div style="font-size: 1.05rem; line-height: 1.85; color: var(--text-secondary);">
                            <p style="font-weight: 700; color: var(--text-primary); font-size: 1.12rem; margin-bottom: 18px;">Dear Members, Colleagues, and Partners,</p>
                            
                            <p style="margin-bottom: 18px;">It is an honor to serve as President of the <strong>Somali Cardiac Society (SCS)</strong>. I am grateful for the trust given to me and look forward to working together with our members and partners to improve cardiovascular health in Somalia.</p>
                            
                            <p style="margin-bottom: 18px;">Heart disease is a growing health challenge in our country. As a professional society, we have an important role in bringing cardiovascular professionals together, improving knowledge and skills, supporting research, raising public awareness, and promoting better standards of cardiovascular care.</p>
                            
                            <p style="margin-bottom: 24px;">Our focus is clear: to strengthen <strong>education and training</strong>, encourage <strong>cardiovascular research</strong>, support <strong>prevention and public awareness</strong>, and build strong <strong>national and international partnerships</strong> with leading cardiology organizations globally.</p>

                            <blockquote style="background: var(--bg-gray); border-left: 4px solid var(--primary-blue); padding: 20px 24px; border-radius: 0 12px 12px 0; margin-bottom: 24px; font-style: italic; color: var(--text-primary); font-weight: 500;">
                                "Together, through collaboration, dedication, and professional excellence, we can make a meaningful difference in the lives of our patients and build a stronger, healthier future for Somalia."
                            </blockquote>

                            <p style="margin-bottom: 0; font-weight: 600; color: var(--text-primary);">Thank you for your trust and support.</p>
                        </div>
                    </div>

                    <div style="margin-top: 36px; padding-top: 24px; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                        <div>
                            <h4 style="font-weight: 800; color: var(--text-primary); font-size: 1.1rem; margin: 0 0 2px 0;">Dr. Abdullahi Mohamed Hassan (Fujeyra)</h4>
                            <p style="color: var(--primary-red); font-weight: 700; font-size: 0.88rem; margin: 0;">President, Somali Cardiac Society (SCS)</p>
                        </div>
                        <img src="<?php echo SITE_URL; ?>/images/logo.png" alt="SCS Logo" style="height: 42px; width: auto; opacity: 0.85;">
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
