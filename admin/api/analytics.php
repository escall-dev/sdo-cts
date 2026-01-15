<?php
/**
 * API: Analytics data
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../models/ComplaintAdmin.php';

$auth = auth();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!$auth->hasPermission('reports.view') && !$auth->hasPermission('complaints.view')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

function normalizeFilterValue($value) {
    $value = trim((string)$value);
    return $value === '' ? null : $value;
}

function buildForecast($monthly) {
    $points = [];
    foreach ($monthly as $row) {
        if (!empty($row['period'])) {
            $points[] = ['period' => $row['period'], 'total' => (int)$row['total']];
        }
    }

    $count = count($points);
    if ($count < 2) {
        return [];
    }

    $sumX = 0;
    $sumY = 0;
    $sumXY = 0;
    $sumXX = 0;
    for ($i = 0; $i < $count; $i++) {
        $x = $i;
        $y = $points[$i]['total'];
        $sumX += $x;
        $sumY += $y;
        $sumXY += $x * $y;
        $sumXX += $x * $x;
    }

    $denominator = ($count * $sumXX) - ($sumX * $sumX);
    if ($denominator == 0) {
        return [];
    }

    $slope = (($count * $sumXY) - ($sumX * $sumY)) / $denominator;
    $intercept = ($sumY - ($slope * $sumX)) / $count;

    $lastPeriod = end($points)['period'];
    $date = DateTime::createFromFormat('Y-m', $lastPeriod);
    if (!$date) {
        return [];
    }

    $forecast = [];
    for ($i = 1; $i <= 3; $i++) {
        $date->modify('+1 month');
        $x = $count - 1 + $i;
        $estimate = max(0, round($intercept + ($slope * $x)));
        $forecast[] = [
            'period' => $date->format('Y-m'),
            'total' => (int)$estimate
        ];
    }

    return $forecast;
}

function buildIncreasingTypes($trends) {
    $byPeriod = [];
    foreach ($trends as $row) {
        $period = $row['period'] ?? null;
        $type = $row['type'] ?? null;
        if (!$period || !$type) {
            continue;
        }
        $byPeriod[$period][$type] = (int)$row['total'];
    }

    if (count($byPeriod) < 2) {
        return [];
    }

    $periods = array_keys($byPeriod);
    sort($periods);
    $latest = $periods[count($periods) - 1];
    $previous = $periods[count($periods) - 2];

    $result = [];
    foreach (COMPLAINT_TYPE_LABELS as $type => $label) {
        $prev = $byPeriod[$previous][$type] ?? 0;
        $curr = $byPeriod[$latest][$type] ?? 0;
        if ($curr > $prev) {
            $result[] = [
                'type' => $type,
                'label' => $label,
                'previous' => $prev,
                'current' => $curr,
                'delta' => $curr - $prev
            ];
        }
    }

    return $result;
}

$filters = [
    'unit' => normalizeFilterValue($_GET['unit'] ?? ''),
    'department' => normalizeFilterValue($_GET['department'] ?? ''),
    'school' => normalizeFilterValue($_GET['school'] ?? ''),
    'status' => normalizeFilterValue($_GET['status'] ?? ''),
    'date_from' => normalizeFilterValue($_GET['date_from'] ?? ''),
    'date_to' => normalizeFilterValue($_GET['date_to'] ?? ''),
    'target_days' => normalizeFilterValue($_GET['target_days'] ?? '')
];

try {
    $complaintModel = new ComplaintAdmin();
    $analytics = $complaintModel->getAnalytics($filters);

    foreach ($analytics['units']['resolution'] as &$unitRow) {
        $total = (int)($unitRow['total'] ?? 0);
        $resolved = (int)($unitRow['resolved_total'] ?? 0);
        $unitRow['resolution_rate'] = $total > 0 ? round(($resolved / $total) * 100, 1) : 0;
    }
    unset($unitRow);

    $analytics['trends'] = [
        'increasing_types' => buildIncreasingTypes($analytics['types']['trends']),
        'forecast' => buildForecast($analytics['volume']['monthly'])
    ];

    echo json_encode([
        'success' => true,
        'filters' => $filters,
        'options' => [
            'units' => UNITS,
            'departments' => ['OSDS', 'SGOD', 'CID', 'Others'],
            'statuses' => array_keys(STATUS_CONFIG),
            'types' => COMPLAINT_TYPE_LABELS
        ],
        'analytics' => $analytics
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load analytics data'
    ]);
}
