<?php
/**
 * Public Header — Somali Cardiac Society
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$navDark = true; // Dark navbar header across all pages so mobile logo & hamburger are white on dark navy
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? e($pageDescription) : 'Somali Cardiac Society — Advancing cardiovascular health care in Somalia through research, education, and clinical excellence.'; ?>">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' — Somali Cardiac Society' : 'Somali Cardiac Society'; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/images/logo-2.png">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=2.5">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-dark" id="navbar">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/index.php" class="nav-logo">
            <img src="<?php echo SITE_URL; ?>/images/logo.png" alt="SCS Logo">
        </a>

        <div class="nav-links" id="navLinks">
            <a href="<?php echo SITE_URL; ?>/index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a>
            
            <!-- About SCS Dropdown -->
            <div class="nav-dual <?php echo in_array($currentPage, ['about.php', 'president-message.php']) ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>/about.php" class="nav-trigger">
                    <span>About Us</span>
                    <span class="nav-caret"></span>
                </a>
                <div class="dropdown-menu">
                    <a href="<?php echo SITE_URL; ?>/about.php"><span>About SCS</span></a>
                    <a href="<?php echo SITE_URL; ?>/president-message.php"><span>President's Message</span></a>
                </div>
            </div>

            <a href="<?php echo SITE_URL; ?>/members.php" class="<?php echo $currentPage === 'members.php' ? 'active' : ''; ?>">Leadership</a>

            <!-- Resources Dropdown -->
            <div class="nav-dual <?php echo $currentPage === 'resources.php' ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>/resources.php" class="nav-trigger">
                    <span>Resources</span>
                    <span class="nav-caret"></span>
                </a>
                <div class="dropdown-menu">
                    <a href="<?php echo SITE_URL; ?>/resources.php?category=guidelines"><span>Clinical Guidelines</span></a>
                    <a href="<?php echo SITE_URL; ?>/resources.php?category=research"><span>Research &amp; Publications</span></a>
                    <a href="<?php echo SITE_URL; ?>/resources.php?category=education"><span>Education &amp; Training</span></a>
                </div>
            </div>

            <a href="<?php echo SITE_URL; ?>/news-events.php" class="<?php echo $currentPage === 'news-events.php' ? 'active' : ''; ?>">News &amp; Events</a>

            <!-- Conference Dropdown -->
            <div class="nav-dual <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/conference') !== false) ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>/conference" class="nav-trigger">
                    <span>Conference</span>
                    <span class="nav-caret"></span>
                </a>
                <div class="dropdown-menu">
                    <a href="<?php echo SITE_URL; ?>/conference"><span>This Year's Conference</span></a>
                    <a href="<?php echo SITE_URL; ?>/conference/past"><span>Past Conferences</span></a>
                </div>
            </div>

            <a href="<?php echo SITE_URL; ?>/contact.php" class="<?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>">Contact Us</a>
        </div>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span style="background-color: #ffffff !important; display: block; width: 20px; height: 2px; margin: 3px 0; border-radius: 2px;"></span>
            <span style="background-color: #ffffff !important; display: block; width: 20px; height: 2px; margin: 3px 0; border-radius: 2px;"></span>
            <span style="background-color: #ffffff !important; display: block; width: 20px; height: 2px; margin: 3px 0; border-radius: 2px;"></span>
        </button>
    </div>
</nav>
