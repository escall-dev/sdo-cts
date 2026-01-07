<?php
/**
 * API: Get Notification Counts
 * Returns counts for sidebar badges (pending complaints, etc.)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../models/ComplaintAdmin.php';

$auth = auth();

// Check if user is authenticated
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $complaintModel = new ComplaintAdmin();
    $stats = $complaintModel->getStatistics();
    
    // Get the last check time from session or default to null
    $lastCheckTime = $_SESSION['last_notification_check'] ?? null;
    $_SESSION['last_notification_check'] = date('Y-m-d H:i:s');
    
    // Count new complaints since last check
    $newCount = 0;
    if ($lastCheckTime) {
        $newCount = $complaintModel->getNewComplaintsSince($lastCheckTime);
    }
    
    $response = [
        'success' => true,
        'counts' => [
            'complaints' => $stats['by_status']['pending'] ?? 0,
            'dashboard' => $stats['this_week'] ?? 0,
            'total' => $stats['total'] ?? 0
        ],
        'hasNew' => $newCount > 0,
        'newCount' => $newCount,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch notification counts'
    ]);
}
