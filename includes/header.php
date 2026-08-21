<?php
/**
 * Public Header — Somali Cardiac Society
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$navDark = isset($navDark) ? $navDark : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? e($pageDescription) : 'Somali Cardiac Society — Advancing cardiovascular health care in Somalia through research, education, and clinical excellence.'; ?>">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' — Somali Cardiac Society' : 'Somali Cardiac Society'; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/images/logo-2.png">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</head>
<body>

<!-- Navigation -->
<nav class="navbar<?php echo $navDark ? ' navbar-dark' : ''; ?>" id="navbar">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/index.php" class="nav-logo">
            <img src="<?php echo SITE_URL; ?>/images/logo.png" alt="SCS Logo">
            <!-- <?php if ($navDark): ?>
            <span class="nav-logo-text">Somali Society<br>of Cardiology</span>
            <?php endif; ?> -->
        </a>

        <div class="nav-links" id="navLinks">
            <a href="<?php echo SITE_URL; ?>/index.php"   class="<?php echo $currentPage === 'index.php'   ? 'active' : ''; ?>">Home</a>
            <a href="<?php echo SITE_URL; ?>/about.php"   class="<?php echo $currentPage === 'about.php'   ? 'active' : ''; ?>">About</a>
            <a href="<?php echo SITE_URL; ?>/members.php" class="<?php echo $currentPage === 'members.php' ? 'active' : ''; ?>">Members</a>
            <div class="nav-dual <?php echo $currentPage === 'content.php' ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>/content.php" class="nav-trigger">
                    <span>Publications</span>
                    <span class="nav-caret"></span>
                </a>
                <div class="dropdown-menu">
                    <a href="<?php echo SITE_URL; ?>/content.php"><span>Research &amp; Education</span></a>
                    <a href="<?php echo SITE_URL; ?>/content.php"><span>News &amp; Events</span></a>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="<?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>">Contact Us</a>
        </div>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>
