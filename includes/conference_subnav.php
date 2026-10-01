<?php
/**
 * Conference Sub-Navigation Bar
 * ---------------------------------------------------------------
 * Include this file inside every conference sub-page AFTER the
 * main header.php.  It auto-highlights the active link based on
 * the current PHP filename.
 *
 * Dependencies:
 *   - SITE_URL constant must already be defined (via config.php)
 *   - conference.css must be enqueued on the page
 * ---------------------------------------------------------------
 */

// Determine which sub-page we're on
$confSubPage  = basename($_SERVER['PHP_SELF']);
$confBaseUrl  = SITE_URL . '/conference';

// Optional: pull the active conference title + year from DB for branding
$confNavTitle = '';
try {
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT title, year FROM conferences WHERE status = 'active' LIMIT 1"
    );
    $stmt->execute();
    $confNavRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($confNavRecord) {
        $confNavTitle = e($confNavRecord['title']) . ' ' . e($confNavRecord['year']);
    }
} catch (Exception $e) {
    // Silently fail — brand text is optional
    $confNavTitle = 'SCS Conference';
}

// Sub-navigation link definitions  [label, filename]
$subNavItems = [
    ['About Conference',    ''],
    ['Speakers',            'speakers'],
    ['Call for Abstracts',  'call-for-abstracts'],
    ['Registration',        'registration'],
    ['Past Conferences',    'past'],
];
?>

<!-- ============================================================
     Conference Sub-Navbar
     ============================================================ -->
<nav class="conf-subnav" id="confSubnav" aria-label="Conference navigation">
    <div class="conf-subnav-inner container">

        <!-- Brand / conference title -->
        <div class="conf-subnav-brand">
            <a href="<?php echo $confBaseUrl; ?>" class="conf-subnav-home">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <span><?php echo $confNavTitle ?: 'SCS Conference'; ?></span>
                </a>
        </div>

        <!-- Nav links (horizontal on desktop, collapsible on mobile) -->
        <div class="conf-subnav-links" id="confSubLinks" role="list">
            <?php foreach ($subNavItems as [$label, $page]): ?>
                    <a href="<?php echo $confBaseUrl . ($page !== '' ? '/' . $page : ''); ?>"
                   class="conf-subnav-link<?php echo $confSubPage === $page ? ' active' : ''; ?>"
                   role="listitem"
                   <?php echo $confSubPage === $page ? 'aria-current="page"' : ''; ?>>
                    <span><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Mobile hamburger toggle -->
        <button class="conf-subnav-toggle"
                id="confSubToggle"
                aria-label="Toggle conference menu"
                aria-expanded="false"
                aria-controls="confSubLinks">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div><!-- /.conf-subnav-inner -->
</nav><!-- /.conf-subnav -->

<script>
(function () {
    'use strict';
    var toggle = document.getElementById('confSubToggle');
    var links  = document.getElementById('confSubLinks');

    if (!toggle || !links) return;

    // Toggle open/close on mobile
    toggle.addEventListener('click', function () {
        var isOpen = links.classList.toggle('open');
        toggle.classList.toggle('open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    // Close the menu when a link is tapped on mobile
    links.querySelectorAll('.conf-subnav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            links.classList.remove('open');
            toggle.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        });
    });

    // Close when clicking outside
    document.addEventListener('click', function (e) {
        if (!toggle.contains(e.target) && !links.contains(e.target)) {
            links.classList.remove('open');
            toggle.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    // ----------------------------------------------------------------
    // Sticky-shrink: add .scrolled class to the subnav when the user
    // scrolls past the main navbar so we can tighten the padding.
    // ----------------------------------------------------------------
    var subnav = document.getElementById('confSubnav');
    var mainNav = document.getElementById('navbar');

    function onScroll() {
        var navH = mainNav ? mainNav.offsetHeight : 70;
        if (window.scrollY > navH) {
            subnav.classList.add('scrolled');
        } else {
            subnav.classList.remove('scrolled');
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); // run once on load
}());
</script>
