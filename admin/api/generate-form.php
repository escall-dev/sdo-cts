<?php
/**
 * Generate & Download Complaint-Assisted Form as PDF
 * 
 * Usage:  GET /admin/api/generate-form.php?id=<complaint_id>
 *         GET /admin/api/generate-form.php?id=<complaint_id>&format=docx  (for DOCX)
 * 
 * Default format is PDF (converted via LibreOffice).
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../models/ComplaintAdmin.php';
require_once __DIR__ . '/../../services/ComplaintFormGenerator.php';

$auth = auth();
$auth->requirePermission('complaints.view');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing complaint ID']);
    exit;
}

$format = strtolower($_GET['format'] ?? 'pdf');

$complaintModel = new ComplaintAdmin();
$complaint = $complaintModel->getById($id);

if (!$complaint) {
    http_response_code(404);
    echo json_encode(['error' => 'Complaint not found']);
    exit;
}

try {
    $generator = new ComplaintFormGenerator();
    if ($format === 'docx') {
        $generator->download($complaint);
    } else {
        $generator->downloadPdf($complaint);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to generate form: ' . $e->getMessage()]);
    exit;
}
