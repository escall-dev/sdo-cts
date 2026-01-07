<?php
/**
 * API: Activate/Deactivate User
 * Only Super Admin can activate/deactivate users
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../models/AdminUser.php';

$auth = auth();

// Check authentication
if (!$auth->isLoggedIn()) {
    header('Location: /SDO-cts/admin/login.php');
    exit;
}

// Only Super Admin can manage user status
if (!$auth->isSuperAdmin()) {
    $_SESSION['flash_error'] = 'Access denied. Only Super Admin can manage users.';
    header('Location: /SDO-cts/admin/users.php');
    exit;
}

// Verify CSRF token
if (!$auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
    header('Location: /SDO-cts/admin/users.php');
    exit;
}

$userId = intval($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$userId || !in_array($action, ['activate', 'deactivate'])) {
    $_SESSION['flash_error'] = 'Invalid request.';
    header('Location: /SDO-cts/admin/users.php');
    exit;
}

// Prevent self-deactivation
if ($userId === $auth->getUserId()) {
    $_SESSION['flash_error'] = 'You cannot deactivate your own account.';
    header('Location: /SDO-cts/admin/users.php');
    exit;
}

try {
    $userModel = new AdminUser();
    $user = $userModel->getById($userId);
    
    if (!$user) {
        throw new Exception('User not found.');
    }
    
    if ($action === 'activate') {
        $userModel->activate($userId);
        $auth->logActivity('update', 'user', $userId, "Activated user: " . $user['full_name']);
        $_SESSION['flash_success'] = 'User activated successfully.';
    } else {
        $userModel->deactivate($userId);
        $auth->logActivity('update', 'user', $userId, "Deactivated user: " . $user['full_name']);
        $_SESSION['flash_success'] = 'User deactivated successfully.';
    }
    
} catch (Exception $e) {
    $_SESSION['flash_error'] = $e->getMessage();
}

header('Location: /SDO-cts/admin/users.php');
exit;

