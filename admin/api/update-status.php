<?php
/**
 * API: Update Complaint Status
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../models/ComplaintAdmin.php';
require_once __DIR__ . '/../../services/email/ComplaintNotification.php';

$auth = auth();

// Check authentication and permission
if (!$auth->isLoggedIn()) {
    header('Location: /SDO-cts/admin/login.php');
    exit;
}

if (!$auth->hasPermission('complaints.update')) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Verify CSRF token
if (!$auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

$complaintId = intval($_POST['complaint_id'] ?? 0);
$status = $_POST['status'] ?? '';
$notes = trim($_POST['notes'] ?? '');

if (!$complaintId || !$status) {
    $_SESSION['flash_error'] = 'Invalid request.';
    header('Location: /SDO-cts/admin/complaints.php');
    exit;
}

try {
    $complaintModel = new ComplaintAdmin();
    $user = $auth->getUser();
    
    // Get complaint data before update for email notification
    $complaint = $complaintModel->getById($complaintId);
    
    // Determine if this is a progress update (same status) or a status change
    $isProgressUpdate = ($complaint && $complaint['status'] === $status);
    
    // Require notes for progress updates
    if ($isProgressUpdate && empty($notes)) {
        $_SESSION['flash_error'] = 'Please provide a progress note.';
        $referer = $_SERVER['HTTP_REFERER'] ?? '/SDO-cts/admin/complaints.php';
        header('Location: ' . $referer);
        exit;
    }
    
    $complaintModel->updateStatus($complaintId, $status, $notes, $user['id'], $user['full_name']);
    
    // Log activity
    if ($isProgressUpdate) {
        $auth->logActivity('progress_update', 'complaint', $complaintId, 
            "Added progress update for " . $complaint['reference_number']);
    } else {
        $auth->logActivity('status_change', 'complaint', $complaintId, 
            "Changed status to " . STATUS_CONFIG[$status]['label'] . " for " . $complaint['reference_number']);
    }
    
    // Send email notification when complaint is closed
    if ($status === 'closed' && !empty($complaint['email_address'])) {
        try {
            $emailNotification = new ComplaintNotification();
            $emailNotification->sendComplaintResolvedNotification($complaint, $notes);
        } catch (Exception $emailError) {
            // Log email error but don't interrupt the status update process
            error_log("Email notification error on close: " . $emailError->getMessage());
        }
    }
    
    $_SESSION['flash_success'] = $isProgressUpdate 
        ? 'Progress update added successfully.' 
        : 'Status updated successfully.';
    
} catch (Exception $e) {
    $_SESSION['flash_error'] = $e->getMessage();
}

// Redirect back
$referer = $_SERVER['HTTP_REFERER'] ?? '/SDO-cts/admin/complaints.php';
header('Location: ' . $referer);
exit;

