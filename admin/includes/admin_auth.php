<?php
/**
 * AutoPulse - Admin Authentication Guard
 * Protects all administrative pages from unauthorized access.
 */

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!is_admin()) {
    header('Location: login.php?msg=admin_required');
    exit;
}
