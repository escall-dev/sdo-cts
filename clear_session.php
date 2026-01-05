<?php
/**
 * Clear form session data
 */
session_start();

// Clear form data from session
unset($_SESSION['form_data']);
unset($_SESSION['form_files']);

// Clean up temp files
$tempDir = __DIR__ . '/uploads/temp/';
if (is_dir($tempDir)) {
    $sessionId = session_id();
    $files = glob($tempDir . $sessionId . '_*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

// Return success
header('Content-Type: application/json');
echo json_encode(['success' => true]);

