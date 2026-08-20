<?php
/**
 * Home Page — Somali Cardiac Society
 */
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'Home';
$pageDescription = 'Somali Cardiac Society — Advancing cardiovascular health care in Somalia through research, education, and clinical excellence.';
$navDark = true;

// Fetch stats and recent content
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM content WHERE is_published = 1 ORDER BY created_at DESC LIMIT 3");
    $recentContent = $stmt->fetchAll();

    $memberCount  = $db->query("SELECT COUNT(*) FROM members WHERE is_active = 1")->fetchColumn();
    $contentCount = $db->query("SELECT COUNT(*) FROM content WHERE is_published = 1")->fetchColumn();
} catch (Exception $ex) {
    $recentContent = [];
    $memberCount   = 0;
    $contentCount  = 0;
}

include __DIR__ . '/includes/header.php';
?>

<!-- =========================================
     HERO — Full Background Image with Overlay
     ========================================= -->
<section class="hero" id="hero">
    <div class="container">
        <div class="hero-row">

            <!-- Left: Text Content over Gradient Overlay -->
            <div class="hero-content fade-in">
                <div class="hero-badge">
                    <span class="dot"></span>
                    Somali Society of Cardiology — Est. 2023
                </div>

                <h1>Advancing <span class="highlight">Cardiovascular Health</span><br>in Somalia</h1>

                <p class="hero-desc">
                    Uniting healthcare professionals to promote the highest standards of cardiac care through research, education, and clinical excellence across Somalia.
                </p>

                <div class="hero-buttons">
                    <a href="about.php" class="btn btn-hero-primary btn-lg">
                        <span class="btn-icon-circle">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                        Learn About Us
                    </a>
                    <a href="members.php" class="btn btn-hero-outline btn-lg">
                        <span class="btn-icon-plain">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        </span>
                        Meet Our Specialists
                    </a>
                </div>
            </div>

        </div><!-- /.hero-row -->
    </div><!-- /.container -->
</section>

<!-- Stats Ribbon -->
<section class="stats-ribbon">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card fade-in">
                <div class="stat-icon">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <div class="stat-number" data-count="<?php echo $memberCount ?: 50; ?>" data-suffix="+">0</div>
                <div class="stat-label">Registered Specialists</div>
            </div>
            <div class="stat-card fade-in">
                <div class="stat-icon" style="background:var(--primary-red-light);color:var(--primary-red);">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
                </div>
                <div class="stat-number" data-count="<?php echo $contentCount ?: 25; ?>" data-suffix="+">0</div>
                <div class="stat-label">Published Resources</div>
            </div>
            <div class="stat-card fade-in">
                <div class="stat-icon">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </div>
                <div class="stat-number" data-count="3" data-suffix="">0</div>
                <div class="stat-label">Years of Service</div>
            </div>
            <div class="stat-card fade-in">
                <div class="stat-icon" style="background:var(--primary-red-light);color:var(--primary-red);">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
                </div>
                <div class="stat-number" data-count="12" data-suffix="+">0</div>
                <div class="stat-label">Events &amp; Workshops</div>
            </div>
        </div>
    </div>
</section>

<!-- About Snapshot -->
<section class="about-feature">
    <div class="container about-feature-wrap">
        <div class="about-feature-content fade-in">
            <h2>About the Somali Cardiac Society</h2>
            <p>The Somali Society of Cardiology was founded in 2023 to unite healthcare professionals who share a common interest in cardiovascular diseases across Somalia.</p>
            <p>Our society brings together physicians, medical officers, nurses, cardiologists, and other healthcare providers committed to advancing cardiovascular health through research, education, and clinical excellence.</p>
            <a href="about.php" class="btn btn-primary btn-lg">See More About Us</a>
        </div>
        <div class="about-feature-image fade-in">
            <img src="<?php echo SITE_URL; ?>/images/profile.jpeg" alt="Cardiac Society" class="feature-image">
        </div>
    </div>
</section>

<!-- Latest Updates -->
<section class="section section-gray">
    <div class="container">
        <div class="section-header fade-in">
            <h2>Latest Updates</h2>
            <p>Stay informed with our latest research publications, educational programs, news, and upcoming cardiac events.</p>
            <div class="section-line"></div>
        </div>
        <div class="content-grid">
            <?php if (!empty($recentContent)): ?>
                <?php foreach ($recentContent as $item): ?>
                <div class="card fade-in">
                    <?php if ($item['feature_image']): ?>
                        <img src="<?php echo UPLOADS_URL . '/' . e($item['feature_image']); ?>" alt="<?php echo e($item['title']); ?>" class="card-image">
                    <?php else: ?>
                        <div class="card-image" style="display:flex;align-items:center;justify-content:center;">
                            <svg width="40" height="40" fill="var(--primary-blue)" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="card-badge badge-<?php echo e($item['category']); ?>"><?php echo e(ucfirst($item['category'])); ?></span>
                        <h3 class="card-title"><?php echo e($item['title']); ?></h3>
                        <p class="card-text"><?php echo e(substr($item['summary'] ?? '', 0, 120)); ?>...</p>
                        <div class="card-meta">
                            <span>
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                                <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                            </span>
                            <a href="content.php?slug=<?php echo e($item['slug']); ?>" style="color:var(--primary-blue);font-weight:600;">Read More →</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card fade-in">
                    <div class="card-image" style="display:flex;align-items:center;justify-content:center;"><svg width="40" height="40" fill="var(--primary-blue)" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></div>
                    <div class="card-body"><span class="card-badge badge-research">Research</span><h3 class="card-title">CVD Prevention in Somalia</h3><p class="card-text">Comprehensive studies on CVD prevention strategies.</p></div>
                </div>
                <div class="card fade-in">
                    <div class="card-image" style="display:flex;align-items:center;justify-content:center;"><svg width="40" height="40" fill="var(--primary-red)" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></div>
                    <div class="card-body"><span class="card-badge badge-news">News</span><h3 class="card-title">New Cardiac Lab in Mogadishu</h3><p class="card-text">State-of-the-art catheterization labs now available locally.</p></div>
                </div>
                <div class="card fade-in">
                    <div class="card-image" style="display:flex;align-items:center;justify-content:center;"><svg width="40" height="40" fill="var(--primary-blue)" viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg></div>
                    <div class="card-body"><span class="card-badge badge-events">Events</span><h3 class="card-title">Annual Cardiology Conference 2026</h3><p class="card-text">Join us for the premier cardiac event in East Africa.</p></div>
                </div>
            <?php endif; ?>
        </div>
        <div style="text-align:center;margin-top:44px;" class="fade-in">
            <a href="content.php" class="btn btn-primary btn-lg">View All Updates</a>
        </div>
    </div>
</section>

<!-- Quick Contact CTA -->
<section class="quick-contact">
    <div class="container fade-in">
        <h3>Need Cardiac Care Information?</h3>
        <p>Get in touch with the Somali Cardiac Society for inquiries about cardiovascular health services in Somalia.</p>
        <a href="contact.php" class="btn btn-lg" style="background:#27AAE1;color:#fff;font-weight:700;box-shadow:0 6px 24px rgba(39,170,225,0.4);">Contact Us Today</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
