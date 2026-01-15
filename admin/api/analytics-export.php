<?php
/**
 * API: Analytics CSV Export
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

$auth = auth();

if (!$auth->isLoggedIn()) {
    header('Location: /SDO-cts/admin/login.php');
    exit;
}

if (!$auth->hasPermission('reports.view') && !$auth->hasPermission('complaints.view')) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

function normalizeFilterValue($value) {
    $value = trim((string)$value);
    return $value === '' ? null : $value;
}

function buildExportWhere($filters) {
    $where = " WHERE 1=1";
    $params = [];

    if (!empty($filters['status'])) {
        $where .= " AND c.status = ?";
        $params[] = $filters['status'];
    }

    if (!empty($filters['unit'])) {
        $where .= " AND c.assigned_unit = ?";
        $params[] = $filters['unit'];
    }

    if (!empty($filters['department'])) {
        $where .= " AND c.referred_to = ?";
        $params[] = $filters['department'];
    }

    if (!empty($filters['school'])) {
        $where .= " AND c.involved_school_office_unit = ?";
        $params[] = $filters['school'];
    }

    if (!empty($filters['date_from'])) {
        $where .= " AND DATE(c.created_at) >= ?";
        $params[] = $filters['date_from'];
    }

    if (!empty($filters['date_to'])) {
        $where .= " AND DATE(c.created_at) <= ?";
        $params[] = $filters['date_to'];
    }

    return [$where, $params];
}

function diffHours($start, $end) {
    if (!$start || !$end) {
        return null;
    }
    $startTs = strtotime($start);
    $endTs = strtotime($end);
    if ($startTs === false || $endTs === false) {
        return null;
    }
    return round(($endTs - $startTs) / 3600, 2);
}

$filters = [
    'unit' => normalizeFilterValue($_GET['unit'] ?? ''),
    'department' => normalizeFilterValue($_GET['department'] ?? ''),
    'school' => normalizeFilterValue($_GET['school'] ?? ''),
    'status' => normalizeFilterValue($_GET['status'] ?? ''),
    'date_from' => normalizeFilterValue($_GET['date_from'] ?? ''),
    'date_to' => normalizeFilterValue($_GET['date_to'] ?? '')
];

[$whereSql, $params] = buildExportWhere($filters);

$db = Database::getInstance();
$rows = $db->query(
    "SELECT c.id, c.reference_number, c.created_at, c.status,
            c.referred_to, c.referred_to_other, c.assigned_unit, c.involved_school_office_unit,
            c.accepted_at,
            (SELECT MIN(created_at) FROM complaint_history ch WHERE ch.complaint_id = c.id AND ch.status IN ('accepted','in_progress','resolved','closed')) as first_response_at,
            (SELECT MIN(created_at) FROM complaint_history ch WHERE ch.complaint_id = c.id AND ch.status IN ('resolved','closed')) as resolved_at
     FROM complaints c {$whereSql}
     ORDER BY c.created_at DESC",
    $params
)->fetchAll();

$filename = 'complaint_analytics_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fputcsv($output, [
    'Reference',
    'Created At',
    'Status',
    'Referred To',
    'Referred Other',
    'Assigned Unit',
    'Filed Unit',
    'Complaint Type',
    'First Response Hours',
    'Resolution Hours',
    'Days Open',
    'Accepted At',
    'Resolved At'
]);

foreach ($rows as $row) {
    $type = getComplaintType($row['referred_to'], $row['referred_to_other']);
    $firstResponse = $row['accepted_at'] ?: $row['first_response_at'];
    $firstResponseHours = diffHours($row['created_at'], $firstResponse);
    $resolutionHours = diffHours($row['created_at'], $row['resolved_at']);
    $daysOpen = null;
    if (!empty($row['created_at'])) {
        $end = $row['resolved_at'] ?: date('Y-m-d H:i:s');
        $daysOpen = floor((strtotime($end) - strtotime($row['created_at'])) / 86400);
    }

    fputcsv($output, [
        $row['reference_number'],
        $row['created_at'],
        $row['status'],
        $row['referred_to'],
        $row['referred_to_other'],
        $row['assigned_unit'],
        $row['involved_school_office_unit'],
        COMPLAINT_TYPE_LABELS[$type] ?? ucfirst($type),
        $firstResponseHours,
        $resolutionHours,
        $daysOpen,
        $row['accepted_at'],
        $row['resolved_at']
    ]);
}

fclose($output);
exit;
