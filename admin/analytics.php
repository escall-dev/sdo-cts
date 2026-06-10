<?php
/**
 * Analytics Dashboard
 * SDO CTS - Admin Analytics
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../models/ComplaintAdmin.php';

$auth = auth();
$auth->requireLogin();

if (!$auth->hasPermission('reports.view') && !$auth->hasPermission('complaints.view')) {
    header('HTTP/1.1 403 Forbidden');
    include __DIR__ . '/403.php';
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="/CTS/admin/assets/css/analytics.css">

<div class="analytics-page" id="analytics-page">
    <div class="page-header">
        <div>
            <h2>Analytics Overview</h2>
            <p class="page-subtitle">Track complaint volumes, response times, and unit performance.</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-outline btn-sm" id="exportCsvBtn"><i class="fas fa-file-csv"></i> Export CSV</button>
            <button class="btn btn-outline btn-sm" id="exportPdfBtn"><i class="fas fa-file-pdf"></i> Export PDF</button>
            <button class="btn btn-primary btn-sm" id="printReportBtn"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>

    <div class="analytics-tabs" id="analyticsTabs">
        <button class="tab-button active" data-target="volume">Volume</button>
        <button class="tab-button" data-target="status">Status</button>
        <button class="tab-button" data-target="response">Response</button>
        <button class="tab-button" data-target="categories">Categories</button>
        <button class="tab-button" data-target="units">Units</button>
        <button class="tab-button" data-target="locations">Locations</button>
        <button class="tab-button" data-target="trends">Trends</button>
        <button class="tab-button" data-target="exports">Exports</button>
    </div>

    <div class="filter-bar">
        <form class="filter-form" id="analyticsFilterForm">
            <div class="filter-group">
                <label for="filterDepartment">Department</label>
                <select class="filter-select" id="filterDepartment" name="department">
                    <option value="">All Departments</option>
                    <option value="OSDS">OSDS</option>
                    <option value="SGOD">SGOD</option>
                    <option value="CID">CID</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filterUnit">Assigned Unit</label>
                <select class="filter-select" id="filterUnit" name="unit">
                    <option value="">All Units</option>
                    <?php foreach (UNITS as $unitCode => $unitLabel): ?>
                        <option value="<?php echo htmlspecialchars($unitCode); ?>">
                            <?php echo htmlspecialchars($unitLabel); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="filterSchool">School / Section</label>
                <input type="text" class="filter-input" id="filterSchool" name="school"
                    placeholder="Type school or section">
            </div>
            <div class="filter-group">
                <label for="filterStatus">Status</label>
                <select class="filter-select" id="filterStatus" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach (STATUS_CONFIG as $statusKey => $statusConfig): ?>
                        <option value="<?php echo $statusKey; ?>"><?php echo $statusConfig['label']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="filterDateFrom">Date From</label>
                <input type="date" class="filter-input" id="filterDateFrom" name="date_from">
            </div>
            <div class="filter-group">
                <label for="filterDateTo">Date To</label>
                <input type="date" class="filter-input" id="filterDateTo" name="date_to">
            </div>
            <div class="filter-group">
                <label for="filterTargetDays">Target Days</label>
                <input type="number" min="1" class="filter-input" id="filterTargetDays" name="target_days" value="14">
            </div>
            <div class="filter-actions">
                <button type="button" class="btn btn-secondary btn-sm" id="resetFiltersBtn">Reset</button>
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            </div>
        </form>
    </div>

    <div class="analytics-grid">
        <section class="dashboard-card" id="volume">
            <div class="card-header">
                <h2><i class="fas fa-chart-line"></i> Complaint Count Analysis</h2>
            </div>
            <div class="card-body analytics-charts">
                <div class="chart-card">
                    <h3>Daily Complaints</h3>
                    <canvas id="dailyVolumeChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Weekly Complaints</h3>
                    <canvas id="weeklyVolumeChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Monthly Complaints</h3>
                    <canvas id="monthlyVolumeChart"></canvas>
                </div>
            </div>
        </section>

        <section class="dashboard-card" id="status">
            <div class="card-header">
                <h2><i class="fas fa-tasks"></i> Status Tracking</h2>
            </div>
            <div class="card-body analytics-two-col">
                <div class="chart-card">
                    <h3>Status Totals</h3>
                    <canvas id="statusTotalsChart"></canvas>
                </div>
                <div class="analytics-table-card">
                    <h3>Average Time to Status (hours)</h3>
                    <div class="table-responsive">
                        <table class="data-table" id="statusAverageTable">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Average Hours</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-card" id="response">
            <div class="card-header">
                <h2><i class="fas fa-stopwatch"></i> Response Time Metrics</h2>
            </div>
            <div class="card-body analytics-metrics">
                <div class="metric-card">
                    <span class="metric-label">Average First Response</span>
                    <span class="metric-value" id="avgFirstResponse">-</span>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Average Resolution</span>
                    <span class="metric-value" id="avgResolution">-</span>
                </div>
                <div class="metric-card highlight">
                    <span class="metric-label">Overdue Complaints</span>
                    <span class="metric-value" id="overdueCount">-</span>
                </div>
            </div>
            <div class="card-body">
                <h3 class="section-title">Overdue List</h3>
                <div class="table-responsive">
                    <table class="data-table" id="overdueTable">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Status</th>
                                <th>Days Open</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="dashboard-card" id="categories">
            <div class="card-header">
                <h2><i class="fas fa-layer-group"></i> Category & Type Analysis</h2>
            </div>
            <div class="card-body analytics-two-col">
                <div class="chart-card compact-chart-card">
                    <h3>Complaint Distribution by Type</h3>
                    <div class="type-chart-wrap">
                        <canvas id="typeDistributionChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>Type Trends Over Time</h3>
                    <canvas id="typeTrendChart"></canvas>
                </div>
            </div>
        </section>

        <section class="dashboard-card" id="units">
            <div class="card-header">
                <h2><i class="fas fa-building"></i> Unit & Department Performance</h2>
            </div>
            <div class="card-body">
                <div class="unit-controls">
                    <div class="toggle-group">
                        <label>View Type</label>
                        <div class="btn-row">
                            <button class="btn btn-secondary btn-sm active" data-view="filed">Filed by Unit</button>
                            <button class="btn btn-secondary btn-sm" data-view="received">Received by Unit</button>
                        </div>
                    </div>
                    <div class="unit-filters">
                        <div class="filter-group">
                            <label for="unitOfficeFilter">Office</label>
                            <select id="unitOfficeFilter" class="filter-select">
                                <option value="">All Offices</option>
                                <?php foreach (array_keys(OFFICE_UNITS) as $office): ?>
                                    <option value="<?php echo htmlspecialchars($office); ?>">
                                        <?php echo htmlspecialchars($office); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="unitUnitFilter">Unit</label>
                            <select id="unitUnitFilter" class="filter-select" disabled>
                                <option value="">-- Select Office First --</option>
                            </select>
                        </div>
                    </div>
                    <div class="sort-group">
                        <label for="unitSort">Sort by</label>
                        <select id="unitSort" class="filter-select">
                            <option value="volume">Complaint Count</option>
                            <option value="response">Handling Time</option>
                            <option value="resolution">Resolution Rate</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="unitPerformanceTable">
                        <thead>
                            <tr>
                                <th>Unit</th>
                                <th>Count</th>
                                <th>Resolution Rate</th>
                                <th>Avg Handling (hrs)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="dashboard-card" id="locations">
            <div class="card-header">
                <h2><i class="fas fa-map-marker-alt"></i> Location / Section Analysis</h2>
            </div>
            <div class="card-body">
                <div class="chart-card location-chart-card">
                    <h3>Complaints by School or Section</h3>
                    <div class="location-chart-wrap">
                        <canvas id="locationChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-card" id="trends">
            <div class="card-header">
                <h2><i class="fas fa-chart-area"></i> Trend & Forecasting</h2>
            </div>
            <div class="card-body analytics-two-col">
                <div class="chart-card">
                    <h3>Monthly Forecast</h3>
                    <canvas id="forecastChart"></canvas>
                </div>
                <div class="analytics-table-card">
                    <h3>Increasing Complaint Types</h3>
                    <div class="table-responsive">
                        <table class="data-table" id="increasingTypesTable">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Previous</th>
                                    <th>Current</th>
                                    <th>Delta</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-card" id="exports">
            <div class="card-header">
                <h2><i class="fas fa-file-export"></i> Export & Reporting</h2>
            </div>
            <div class="card-body report-summary" id="reportSummary">
                <p class="report-note">Use the export buttons above to download filtered analytics for management
                    reporting.</p>
                <div class="summary-grid">
                    <div class="summary-card">
                        <span class="summary-label">Total Complaints</span>
                        <span class="summary-value" id="summaryTotal">-</span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label">Resolved Rate</span>
                        <span class="summary-value" id="summaryResolutionRate">-</span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label">Average Response (hrs)</span>
                        <span class="summary-value" id="summaryResponse">-</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    <?php
    $statusLabels = [];
    foreach (STATUS_CONFIG as $statusKey => $statusConfig) {
        $statusLabels[$statusKey] = $statusConfig['label'];
    }
    ?>
    window.analyticsConfig = {
        statusLabels: <?php echo json_encode($statusLabels); ?>,
        statusOrder: <?php echo json_encode(array_keys(STATUS_CONFIG)); ?>,
        unitLabels: <?php echo json_encode(UNITS); ?>,
        officeUnits: <?php echo json_encode(OFFICE_UNITS); ?>,
        typeLabels: <?php echo json_encode(COMPLAINT_TYPE_LABELS); ?>
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
<script src="/CTS/admin/assets/js/analytics.js?v=<?php echo time(); ?>"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>