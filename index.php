<?php

/**
 * Home Page — Somali Cardiac Society
 *
 * DEBUG MODE
 * Remove or disable these in production after fixing the problem.
 */

// Show PHP errors temporarily
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Convert PHP errors into exceptions so our error handler can display them
set_error_handler(function ($severity, $message, $file, $line) {

    // Respect PHP's current error_reporting setting
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Catch fatal errors that happen outside normal try/catch blocks
register_shutdown_function(function () {

    $error = error_get_last();

    if ($error !== null) {

        $fatalTypes = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR
        ];

        if (in_array($error['type'], $fatalTypes, true)) {

            echo '<div style="
                background:#fff0f0;
                color:#8b0000;
                border:2px solid #ff4d4d;
                padding:24px;
                margin:20px;
                font-family:Arial,sans-serif;
                border-radius:10px;
                position:relative;
                z-index:99999;
                box-shadow:0 4px 15px rgba(0,0,0,.15);
            ">';

            echo '<h2 style="margin-top:0;">PHP Fatal Error</h2>';

            echo '<p><strong>Message:</strong><br>';
            echo htmlspecialchars($error['message']);
            echo '</p>';

            echo '<p><strong>File:</strong><br>';
            echo htmlspecialchars($error['file']);
            echo '</p>';

            echo '<p><strong>Line:</strong><br>';
            echo htmlspecialchars($error['line']);
            echo '</p>';

            echo '</div>';
        }
    }
});


try {

    // Load authentication / configuration
    require_once __DIR__ . '/config/auth.php';

    $pageTitle = 'Home';

    $pageDescription = 'Somali Cardiac Society — Advancing cardiovascular health care in Somalia through research, education, and clinical excellence.';

    $navDark = true;


    // ============================================================
    // DATABASE
    // ============================================================

    try {

        $db = getDB();


        // --------------------------------------------------------
        // Get latest published content
        // --------------------------------------------------------

        $stmt = $db->query("
            SELECT *
            FROM content
            WHERE is_published = 1
            ORDER BY created_at DESC
            LIMIT 3
        ");

        $recentContent = $stmt->fetchAll();


        // --------------------------------------------------------
        // Get active member count
        // --------------------------------------------------------

        $memberCount = $db
            ->query("
                SELECT COUNT(*)
                FROM members
                WHERE is_active = 1
            ")
            ->fetchColumn();


        // --------------------------------------------------------
        // Get published content count
        // --------------------------------------------------------

        $contentCount = $db
            ->query("
                SELECT COUNT(*)
                FROM content
                WHERE is_published = 1
            ")
            ->fetchColumn();


    } catch (Throwable $ex) {

        // ========================================================
        // DATABASE ERROR
        // ========================================================

        $recentContent = [];
        $memberCount = 0;
        $contentCount = 0;


        echo '<div style="
            background:#fff0f0;
            color:#8b0000;
            border:2px solid #ff4d4d;
            padding:24px;
            margin:20px;
            font-family:Arial,sans-serif;
            border-radius:10px;
            position:relative;
            z-index:99999;
            box-shadow:0 4px 15px rgba(0,0,0,.15);
        ">';

        echo '<h2 style="margin-top:0;">Database Error</h2>';

        echo '<p>
            <strong>Message:</strong><br>
            ' . htmlspecialchars($ex->getMessage()) . '
        </p>';

        echo '<p>
            <strong>File:</strong><br>
            ' . htmlspecialchars($ex->getFile()) . '
        </p>';

        echo '<p>
            <strong>Line:</strong><br>
            ' . htmlspecialchars($ex->getLine()) . '
        </p>';

        echo '<p>
            <strong>Error Type:</strong><br>
            ' . htmlspecialchars(get_class($ex)) . '
        </p>';

        echo '<details style="margin-top:15px;">
            <summary style="cursor:pointer;font-weight:bold;">
                Show Technical Details
            </summary>';

        echo '<pre style="
            background:#1e1e1e;
            color:#fff;
            padding:15px;
            overflow:auto;
            border-radius:6px;
            margin-top:10px;
        ">';

        echo htmlspecialchars($ex->getTraceAsString());

        echo '</pre>';

        echo '</details>';

        echo '</div>';
    }


    // ============================================================
    // HEADER
    // ============================================================

    include __DIR__ . '/includes/header.php';

?>

<!-- =========================================================
     HERO — Full Background Image with Overlay
     ========================================================= -->

<section class="hero" id="hero">

    <div class="container">

        <div class="hero-row">

            <!-- Left: Text Content over Gradient Overlay -->

            <div class="hero-content fade-in">

                <h1>
                    Advancing <span class="highlight">Cardiovascular Health</span><br>in Somalia
                </h1>

                <p class="hero-desc">
                    Dedicated to advancing cardiac care, promoting clinical excellence, and reducing the burden of cardiovascular disease across Somalia.
                </p>

                <div class="hero-buttons">
                    <a href="about.php" class="btn btn-hero-primary btn-lg">
                        <span class="btn-icon-circle">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M5 12h14M13 6l6 6-6 6"/>
                            </svg>
                        </span>
                        Learn About Us
                    </a>

                    <a href="members.php" class="btn btn-hero-outline btn-lg">
                        <span class="btn-icon-plain">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </span>
                        Meet Our Leadership
                    </a>
                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     STATS RIBBON
     ========================================================= -->

<section class="stats-ribbon">

    <div class="container">

        <div class="stats-grid">


            <div class="stat-card fade-in">

                <div class="stat-icon">

                    <svg width="28"
                         height="28"
                         fill="currentColor"
                         viewBox="0 0 24 24">

                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.79 0-3 1.34-3 3s1.21 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.66-4.67-3.5-7-3.5z"/>

                    </svg>

                </div>


                <div class="stat-number"
                     data-count="3"
                     data-suffix="">

                    0

                </div>


                <div class="stat-label">
                    Years of Service
                </div>

            </div>


            <div class="stat-card fade-in">

                <div class="stat-icon"
                     style="background:var(--primary-red-light);color:var(--primary-red);">

                    <svg width="28"
                         height="28"
                         fill="currentColor"
                         viewBox="0 0 24 24">

                        <path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.11.89 2 2 2h14c1.11 0 2-.89 2-2V5c0-1.1-.89-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/>

                    </svg>

                </div>


                <div class="stat-number"
                    
                     data-suffix="+">

                    7+

                </div>


                <div class="stat-label">
                    National & International Collaborations
                </div>

            </div>


            <div class="stat-card fade-in">

                <div class="stat-icon"
                     style="background:var(--primary-red-light);color:var(--primary-red);">

                    <svg width="28"
                         height="28"
                         fill="currentColor"
                         viewBox="0 0 24 24">

                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>

                    </svg>

                </div>


                <div class="stat-number"
                     data-count="<?php echo $contentCount ?: 25; ?>"
                     data-suffix="+">

                    0

                </div>


                <div class="stat-label">
                    Published Resources
                </div>

            </div>


            <div class="stat-card fade-in">

                <div class="stat-icon">

                    <svg width="28"
                         height="28"
                         fill="currentColor"
                         viewBox="0 0 24 24">

                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>

                    </svg>

                </div>


                <div class="stat-number"
                     data-count="12"
                     data-suffix="+">

                    0

                </div>


                <div class="stat-label">
                    Events &amp; Workshops
                </div>

            </div>


        </div>

    </div>

</section>


<!-- =========================================================
     OUR KEY PILLARS
     ========================================================= -->

<section class="about-feature">
    <div class="container">
        <div class="section-header fade-in">
            <h2>Our Strategic Pillars</h2>
            <p>Advancing cardiovascular healthcare across Somalia through three strategic pillars</p>
            <div class="section-line"></div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;" class="fade-in">
            <div style="background: var(--bg-white); padding: 32px 24px; border-radius: var(--radius-md); border: 1px solid var(--border-color); text-align: center; transition: var(--transition);" class="stat-card">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: var(--primary-blue-light); color: var(--primary-blue); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24"><path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg>
                </div>
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 10px;">Governance &amp; Advocacy &amp; Institutional Development & Partnerships</h3>
                <p style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.6;">Build a professional and transparent Society with strong governance, membership, advocacy, national collaboration and international partnerships</p>
            </div>

            <div style="background: var(--bg-white); padding: 32px 24px; border-radius: var(--radius-md); border: 1px solid var(--border-color); text-align: center; transition: var(--transition);" class="stat-card">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: var(--primary-red-light); color: var(--primary-red); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
                </div>
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 10px;">Education, CME, Research, Clinical Standards & Capacity Building</h3>
                <p style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.6;">Improve professional knowledge and skills through CME, public awareness, outreach, research, guidelines and clinical quality improvement</p>
            </div>

            <div style="background: var(--bg-white); padding: 32px 24px; border-radius: var(--radius-md); border: 1px solid var(--border-color); text-align: center; transition: var(--transition);" class="stat-card">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: var(--primary-blue-light); color: var(--primary-blue); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                </div>
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 10px;">Financial Sustainability & Income Generation</h3>
                <p style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.6;">Create diversified, ethical and traceable income to support approved Society activities</p>
            </div>

           
        </div>
    </div>
</section>


<!-- =========================================================
     LATEST UPDATES
     ========================================================= -->

<section class="section section-gray">

    <div class="container">

        <div class="section-header fade-in">

            <h2>
                Latest Updates
            </h2>


            <p>
                Stay informed with our latest research publications,
                educational programs, news, and upcoming cardiac events
            </p>


            <div class="section-line"></div>

        </div>


        <div class="content-grid">

            <?php if (!empty($recentContent)): ?>

                <?php foreach ($recentContent as $item): ?>

                    <div class="card fade-in">

                        <?php if (!empty($item['feature_image'])): ?>

                            <img src="<?php echo UPLOADS_URL . '/' . e($item['feature_image']); ?>"
                                 alt="<?php echo e($item['title']); ?>"
                                 class="card-image">

                        <?php else: ?>

                            <div class="card-image"
                                 style="display:flex;align-items:center;justify-content:center;">

                                <svg width="40"
                                     height="40"
                                     fill="var(--primary-blue)"
                                     viewBox="0 0 24 24">

                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>

                                </svg>

                            </div>

                        <?php endif; ?>


                        <div class="card-body">

                            <span class="card-badge badge-<?php echo e($item['category']); ?>">

                                <?php echo e(ucfirst($item['category'])); ?>

                            </span>


                            <h3 class="card-title">

                                <?php echo e($item['title']); ?>

                            </h3>


                            <p class="card-text">

                                <?php echo e(substr($item['summary'] ?? '', 0, 120)); ?>...

                            </p>


                            <div class="card-meta">

                                <span>

                                    <svg width="14"
                                         height="14"
                                         fill="currentColor"
                                         viewBox="0 0 24 24"
                                         style="vertical-align:middle;margin-right:4px;">

                                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>

                                    </svg>


                                    <?php echo date(
                                        'M d, Y',
                                        strtotime($item['created_at'])
                                    ); ?>

                                </span>


                                <a href="content.php?slug=<?php echo e($item['slug']); ?>"
                                   style="color:var(--primary-blue);font-weight:600;">

                                    Read More →

                                </a>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>


            <?php else: ?>


                <div class="card fade-in">

                    <div class="card-image"
                         style="display:flex;align-items:center;justify-content:center;">

                        <svg width="40"
                             height="40"
                             fill="var(--primary-blue)"
                             viewBox="0 0 24 24">

                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 4.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>

                        </svg>

                    </div>


                    <div class="card-body">

                        <span class="card-badge badge-research">
                            Research
                        </span>

                        <h3 class="card-title">
                            CVD Prevention in Somalia
                        </h3>

                        <p class="card-text">
                            Comprehensive studies on CVD prevention strategies.
                        </p>

                    </div>

                </div>


                <div class="card fade-in">

                    <div class="card-image"
                         style="display:flex;align-items:center;justify-content:center;">

                        <svg width="40"
                             height="40"
                             fill="var(--primary-red)"
                             viewBox="0 0 24 24">

                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>

                        </svg>

                    </div>


                    <div class="card-body">

                        <span class="card-badge badge-news">
                            News
                        </span>

                        <h3 class="card-title">
                            New Cardiac Lab in Mogadishu
                        </h3>

                        <p class="card-text">
                            State-of-the-art catheterization labs now available locally.
                        </p>

                    </div>

                </div>


                <div class="card fade-in">

                    <div class="card-image"
                         style="display:flex;align-items:center;justify-content:center;">

                        <svg width="40"
                             height="40"
                             fill="var(--primary-blue)"
                             viewBox="0 0 24 24">

                            <path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.11.89 2 2 2h14c1.11 0 2-.89 2-2V5c0-1.1-.89-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/>

                        </svg>

                    </div>


                    <div class="card-body">

                        <span class="card-badge badge-events">
                            Events
                        </span>

                        <h3 class="card-title">
                            Annual Cardiology Conference 2026
                        </h3>

                        <p class="card-text">
                            Join us for the premier cardiac event in East Africa.
                        </p>

                    </div>

                </div>


            <?php endif; ?>

        </div>


        <div style="text-align:center;margin-top:44px;"
             class="fade-in">

            <a href="content.php"
               class="btn btn-primary btn-lg">

                View All Updates

            </a>

        </div>

    </div>

</section>


<!-- =========================================================
     QUICK CONTACT CTA
     ========================================================= -->

<section class="quick-contact">

    <div class="container fade-in">

        <h3>
            Work With Us
        </h3>


        <p>
            Connect with the Somali Cardiac Society for collaboration, membership, research, 
           <br> education, and partnerships advancing cardiovascular health in Somalia
        </p>


        <a href="contact.php"
           class="btn btn-lg"
           style="
               background:#27AAE1;
               color:#fff;
               font-weight:700;
               box-shadow:0 6px 24px rgba(39,170,225,0.4);
           ">

            Contact Us Today

        </a>

    </div>

</section>


<?php

// ============================================================
// FOOTER
// ============================================================

include __DIR__ . '/includes/footer.php';


// ============================================================
// END OF PAGE
// ============================================================

} catch (Throwable $ex) {

    // ========================================================
    // GENERAL SYSTEM ERROR
    // ========================================================

    echo '<div style="
        background:#fff0f0;
        color:#8b0000;
        border:2px solid #ff4d4d;
        padding:25px;
        margin:25px;
        font-family:Arial,sans-serif;
        border-radius:10px;
        position:relative;
        z-index:99999;
        box-shadow:0 4px 15px rgba(0,0,0,.15);
    ">';

    echo '<h2 style="margin-top:0;">
        System Error Detected
    </h2>';

    echo '<p>
        <strong>Message:</strong><br>
        ' . htmlspecialchars($ex->getMessage()) . '
    </p>';

    echo '<p>
        <strong>Error Type:</strong><br>
        ' . htmlspecialchars(get_class($ex)) . '
    </p>';

    echo '<p>
        <strong>File:</strong><br>
        ' . htmlspecialchars($ex->getFile()) . '
    </p>';

    echo '<p>
        <strong>Line:</strong><br>
        ' . htmlspecialchars($ex->getLine()) . '
    </p>';

    echo '<details style="margin-top:15px;">

        <summary style="
            cursor:pointer;
            font-weight:bold;
        ">
            Show Full Error Trace
        </summary>';

    echo '<pre style="
        background:#1e1e1e;
        color:#fff;
        padding:15px;
        overflow:auto;
        border-radius:6px;
        margin-top:10px;
        white-space:pre-wrap;
    ">';

    echo htmlspecialchars($ex->getTraceAsString());

    echo '</pre>';

    echo '</details>';

    echo '</div>';
}
?>
