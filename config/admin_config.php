<?php
/**
 * Admin Panel Configuration
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 */

// Session configuration
define('ADMIN_SESSION_NAME', 'SDO_CTS_ADMIN');
define('ADMIN_SESSION_LIFETIME', 3600 * 8); // 8 hours

// Google OAuth Configuration - load from environment variables
// Optional .env loader (project root): reads KEY=VALUE pairs into environment
// This prevents committing secrets and works in local XAMPP setups.
$__envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (is_readable($__envFile)) {
    $lines = file($__envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) { continue; }
        $pos = strpos($line, '=');
        if ($pos === false) { continue; }
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        // Remove optional surrounding quotes
        if ((str_starts_with($val, '"') && str_ends_with($val, '"')) || (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
            $val = substr($val, 1, -1);
        }
        putenv("$key=$val");
        $_ENV[$key] = $val;
    }
}

$GOOGLE_CLIENT_ID = getenv('GOOGLE_CLIENT_ID') ?: '';
$GOOGLE_CLIENT_SECRET = getenv('GOOGLE_CLIENT_SECRET') ?: '';
$GOOGLE_REDIRECT_URI = getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/SDO-cts/admin/auth/google-callback.php';

define('GOOGLE_CLIENT_ID', $GOOGLE_CLIENT_ID);
define('GOOGLE_CLIENT_SECRET', $GOOGLE_CLIENT_SECRET);
define('GOOGLE_REDIRECT_URI', $GOOGLE_REDIRECT_URI);

// Allowed email domains for Google OAuth (leave empty to allow all)
define('ALLOWED_EMAIL_DOMAINS', ['deped-sanpedro.ph', 'deped.gov.ph' , 'gmail.com' ]);

// Admin panel settings
define('ADMIN_TITLE', 'SDO CTS Admin');
define('ADMIN_FULL_TITLE', 'San Pedro Division Office - Complaint Tracking System');
define('ITEMS_PER_PAGE', 15);

// Status workflow configuration
define('STATUS_WORKFLOW', [
    'pending' => ['accepted', 'returned'],
    'accepted' => ['in_progress', 'returned'],
    'in_progress' => ['resolved'],
    'resolved' => ['closed'],
    'returned' => ['pending'],
    'closed' => []
]);

// Status labels and colors
define('STATUS_CONFIG', [
    'pending' => [
        'label' => 'Pending',
        'color' => '#f59e0b',
        'bg' => '#fef3c7',
        'icon' => '<i class="fas fa-clock"></i>'
    ],
    'accepted' => [
        'label' => 'Accepted',
        'color' => '#3b82f6',
        'bg' => '#dbeafe',
        'icon' => '<i class="fas fa-check-circle"></i>'
    ],
    'in_progress' => [
        'label' => 'In Progress',
        'color' => '#8b5cf6',
        'bg' => '#ede9fe',
        'icon' => '<i class="fas fa-spinner"></i>'
    ],
    'resolved' => [
        'label' => 'Resolved',
        'color' => '#10b981',
        'bg' => '#d1fae5',
        'icon' => '<i class="fas fa-check-double"></i>'
    ],
    'returned' => [
        'label' => 'Returned',
        'color' => '#ef4444',
        'bg' => '#fee2e2',
        'icon' => '<i class="fas fa-undo"></i>'
    ],
    'closed' => [
        'label' => 'Closed',
        'color' => '#6b7280',
        'bg' => '#f3f4f6',
        'icon' => '<i class="fas fa-lock"></i>'
    ]
]);

// Unit/Section configuration
define('UNITS', [
    'SDS' => 'SDS: Schools Division Superintendent',
    'ASDS' => 'ASDS: Assistant Schools Division Superintendent',
    'Admin' => 'Admin: Cash, Personnel, Records, Supply, General Services, Procurement',
    'CID' => 'CID: Curriculum Implementation Division (LRMS, Instructional Management, PSDS)',
    'Finance' => 'Finance: Accounting, Budget',
    'ICTO' => 'Information and Communication Technology Office',
    'Legal' => 'Legal Office',
    'SGOD' => 'SGOD: School Governance and Operations Division (M&E, SocMob, Planning & Research, HRD, Facilities, School Health)'
]);

// Complaint type mapping (derived from referred_to + referred_to_other)
define('COMPLAINT_TYPE_MAP', [
    'OSDS' => 'admin',
    'SGOD' => 'facility',
    'CID' => 'academic',
    'Others' => 'admin'
]);

define('COMPLAINT_TYPE_LABELS', [
    'facility' => 'Facility',
    'academic' => 'Academic',
    'it' => 'IT',
    'admin' => 'Admin'
]);

define('COMPLAINT_TYPE_KEYWORDS', [
    'it', 'ict', 'computer', 'network', 'internet', 'wifi', 'email', 'system', 'server'
]);

// Permission definitions
define('PERMISSIONS', [
    'complaints.view' => 'View complaints',
    'complaints.update' => 'Update complaint status',
    'complaints.accept' => 'Accept/Return complaints',
    'complaints.forward' => 'Forward complaints to units',
    'complaints.delete' => 'Delete complaints',
    'users.view' => 'View admin users',
    'users.create' => 'Create admin users',
    'users.update' => 'Update admin users',
    'users.delete' => 'Delete admin users',
    'reports.view' => 'View reports',
    'logs.view' => 'View activity logs',
    'settings.manage' => 'Manage system settings'
]);

// Helper function to get unit display name
if (!function_exists('getUnitDisplayName')) {
    function getUnitDisplayName($unitCode) {
        if (empty($unitCode)) {
            return '-';
        }
        return defined('UNITS') && isset(UNITS[$unitCode]) ? UNITS[$unitCode] : $unitCode;
    }
}

if (!function_exists('getComplaintType')) {
    function getComplaintType($referredTo, $referredOther = '') {
        $type = COMPLAINT_TYPE_MAP[$referredTo] ?? 'admin';
        $other = strtolower(trim((string)$referredOther));
        if ($referredTo === 'Others' && $other !== '') {
            foreach (COMPLAINT_TYPE_KEYWORDS as $keyword) {
                if (strpos($other, $keyword) !== false) {
                    return 'it';
                }
            }
        }
        return $type;
    }
}

