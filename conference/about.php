<?php
require_once __DIR__ . '/../config/db.php';

header('Location: ' . SITE_URL . '/conference', true, 301);
exit;
