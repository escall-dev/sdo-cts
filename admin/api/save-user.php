<?php
/**
 * API: Save User (Create/Update)
 * Only Super Admin can create or update users
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../models/AdminUser.php';

$auth = auth();

// Check authentication
if (!$auth->isLoggedIn()) {
    header('Location: /SDO-cts/admin/login.php');
    exit;
}

// Only Super Admin can create/update users
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
$isEdit = $userId > 0;

// Validate input
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$roleId = intval($_POST['role_id'] ?? 0);
$unit = trim($_POST['unit'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';
$isActive = isset($_POST['is_active']) ? 1 : 0;

$errors = [];

if (empty($fullName)) {
    $errors[] = 'Full name is required.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required.';
}

if (!$roleId) {
    $errors[] = 'Role is required.';
}

if (!$isEdit && empty($password)) {
    $errors[] = 'Password is required for new users.';
}

if ($password && strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}

if ($password && $password !== $passwordConfirm) {
    $errors[] = 'Passwords do not match.';
}

if (!empty($errors)) {
    $_SESSION['flash_error'] = implode(' ', $errors);
    header('Location: /SDO-cts/admin/users.php');
    exit;
}

try {
    $userModel = new AdminUser();
    
    // Check if email exists
    if ($userModel->emailExists($email, $isEdit ? $userId : null)) {
        throw new Exception('A user with this email already exists.');
    }
    
    $data = [
        'full_name' => $fullName,
        'email' => $email,
        'role_id' => $roleId,
        'unit' => $unit ?: null,
        'is_active' => $isActive
    ];
    
    if ($password) {
        $data['password'] = $password;
    }
    
    if ($isEdit) {
        $userModel->update($userId, $data);
        $auth->logActivity('update', 'user', $userId, "Updated user: $fullName");
        $_SESSION['flash_success'] = 'User updated successfully.';
    } else {
        $newUserId = $userModel->create($data, $auth->getUserId());
        $auth->logActivity('create', 'user', $newUserId, "Created user: $fullName");
        $_SESSION['flash_success'] = 'User created successfully.';
    }
    
} catch (Exception $e) {
    $_SESSION['flash_error'] = $e->getMessage();
}

header('Location: /SDO-cts/admin/users.php');
exit;

