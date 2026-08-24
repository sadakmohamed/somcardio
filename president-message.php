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
<div class="page-header" style="background: linear-gradient(135deg, rgba(0,27,46,0.9), rgba(0,40,69,0.92)), url('<?php echo SITE_URL; ?>/images/profile.jpeg') center/cover no-repeat;">
    <div class="container">
        <h1>President's Message</h1>
        <p>Leadership note from the President of the Somali Cardiac Society (SCS)</p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <a href="about.php">About SCS</a>
            <span>›</span>
            <span style="color:rgba(255,255,255,0.8);">President's Message</span>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div style="max-width: 1040px; margin: 0 auto; background: var(--bg-white); border-radius: var(--radius-xl); border: 1px solid var(--border-color); box-shadow: var(--shadow-xl); overflow: hidden;">
            
            <div style="display: grid; grid-template-columns: 340px 1fr; align-items: stretch;" class="president-card-grid">
                
                <!-- Left Column: President Profile Card -->
                <div style="background: linear-gradient(180deg, #001B2E 0%, #002845 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px 28px; text-align: center; border-right: 1px solid rgba(255,255,255,0.08);">
                    <div style="width: 210px; height: 260px; border-radius: var(--radius-lg); overflow: hidden; box-shadow: 0 12px 32px rgba(0,0,0,0.4); border: 3px solid rgba(39, 170, 225, 0.4); margin-bottom: 24px; position: relative;">
                        <img src="<?php echo SITE_URL; ?>/images/profile.jpeg" alt="Dr. Abdullahi Mohamed Hassan (Fujeyra)" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
                    </div>
                    <h3 style="color: #ffffff; font-size: 1.25rem; font-weight: 800; line-height: 1.3; margin-bottom: 4px;">Dr. Abdullahi Mohamed Hassan</h3>
                    <p style="color: var(--primary-blue); font-size: 0.88rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 10px;">(Fujeyra)</p>
                    <div style="display: inline-block; padding: 6px 14px; background: rgba(39, 170, 225, 0.15); border: 1px solid rgba(39, 170, 225, 0.3); border-radius: 50px; color: #ffffff; font-size: 0.8rem; font-weight: 600;">
                        President, Somali Cardiac Society
                    </div>
                </div>

                <!-- Right Column: Official Message Text -->
                <div style="padding: 52px 48px; display: flex; flex-direction: column; justify-content: space-between; background: #ffffff;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid var(--primary-blue-light);">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--primary-blue-light); color: var(--primary-blue); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/></svg>
                            </div>
                            <div>
                                <h2 style="color: var(--text-primary); font-size: 1.85rem; font-weight: 800; margin: 0; line-height: 1.2;">President’s Message</h2>
                                <p style="color: var(--text-light); font-size: 0.88rem; margin: 2px 0 0 0;">Official address to SCS members, colleagues, and international partners</p>
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
