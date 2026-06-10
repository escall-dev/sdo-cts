<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/includes/auth.php';

$auth = auth();
$auth->logout();

header('Location: /CTS/admin/login.php');
exit;

