<?php
/**
 * Email Logs Admin Page
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../models/EmailLog.php';
require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/../config/database.php';

$auth = auth();

// Check authentication
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Only admins can view email logs
if (!$auth->hasPermission('settings.view')) {
    header('Location: 403.php');
    exit;
}

$emailLogModel = new EmailLog();

// Handle filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'event_type' => $_GET['event_type'] ?? '',
    'recipient' => $_GET['recipient'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;

$logs = $emailLogModel->getAll($filters, $page, $perPage);
$totalLogs = $emailLogModel->getCount($filters);
$totalPages = ceil($totalLogs / $perPage);

// Stats based on the same filters used for the table (exclude status for breakdown)
$stats = [
    'total_sent' => 0,
    'total_failed' => 0,
    'total_skipped' => 0,
    'today' => 0
];

$statFilters = $filters;
unset($statFilters['status']);

$stats['total_sent'] = $emailLogModel->getCount(array_merge($statFilters, ['status' => 'sent']));
$stats['total_failed'] = $emailLogModel->getCount(array_merge($statFilters, ['status' => 'failed']));
$stats['total_skipped'] = $emailLogModel->getCount(array_merge($statFilters, ['status' => 'skipped']));

$today = date('Y-m-d');
$todayFilters = $statFilters;
$todayFilters['date_from'] = $today;
$todayFilters['date_to'] = $today;
$stats['today'] = $emailLogModel->getCount($todayFilters);

// Include header
include __DIR__ . '/includes/header.php';
?>

<div class="content-header">
    <div class="header-left">
        <h1><i class="fas fa-envelope"></i> Email Logs</h1>
        <p class="subtitle">View all email notification logs</p>
    </div>
    <div class="header-actions">
        <span class="badge <?php echo MAIL_ENABLED ? 'badge-success' : 'badge-warning'; ?>">
            <?php echo MAIL_ENABLED ? 'Email Enabled' : 'Email Disabled'; ?>
        </span>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background: #10b981;">
            <i class="fas fa-check"></i>
        </div>
        <div class="stat-info">
            <span class="stat-value"><?php echo number_format($stats['total_sent'] ?? 0); ?></span>
            <span class="stat-label">Emails Sent</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #ef4444;">
            <i class="fas fa-times"></i>
        </div>
        <div class="stat-info">
            <span class="stat-value"><?php echo number_format($stats['total_failed'] ?? 0); ?></span>
            <span class="stat-label">Failed</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #f59e0b;">
            <i class="fas fa-forward"></i>
        </div>
        <div class="stat-info">
            <span class="stat-value"><?php echo number_format($stats['total_skipped'] ?? 0); ?></span>
            <span class="stat-label">Skipped</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #1b4a9a;">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-info">
            <span class="stat-value"><?php echo number_format($stats['today'] ?? 0); ?></span>
            <span class="stat-label">Today</span>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="sent" <?php echo $filters['status'] === 'sent' ? 'selected' : ''; ?>>Sent</option>
                        <option value="failed" <?php echo $filters['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="skipped" <?php echo $filters['status'] === 'skipped' ? 'selected' : ''; ?>>Skipped</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Event Type</label>
                    <input type="text" name="event_type" class="form-control" placeholder="e.g., complaint_submitted" value="<?php echo htmlspecialchars($filters['event_type']); ?>">
                </div>
                <div class="filter-group">
                    <label>Recipient</label>
                    <input type="text" name="recipient" class="form-control" placeholder="Email address" value="<?php echo htmlspecialchars($filters['recipient']); ?>">
                </div>
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                </div>
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                </div>
                <div class="filter-group filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="email-logs.php" class="btn btn-outline">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Email Logs Table -->
<div class="card">
    <div class="card-header">
        <h3>Email Log History</h3>
        <span class="text-muted"><?php echo number_format($totalLogs); ?> total records</span>
    </div>
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No email logs found</h3>
                <p>No emails have been sent yet or no records match your filters.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Recipient</th>
                            <th>Subject</th>
                            <th>Event Type</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <span class="date-text"><?php echo date('M j, Y', strtotime($log['created_at'])); ?></span>
                                    <span class="time-text"><?php echo date('g:i A', strtotime($log['created_at'])); ?></span>
                                </td>
                                <td>
                                    <span class="email-text" title="<?php echo htmlspecialchars($log['recipient_email']); ?>">
                                        <?php echo htmlspecialchars($log['recipient_email']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="subject-text" title="<?php echo htmlspecialchars($log['subject']); ?>">
                                        <?php echo htmlspecialchars(substr($log['subject'], 0, 40)); ?>
                                        <?php echo strlen($log['subject']) > 40 ? '...' : ''; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo htmlspecialchars($log['event_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($log['reference_number']): ?>
                                        <a href="complaint-view.php?id=<?php echo $log['reference_id']; ?>" class="ref-link">
                                            <?php echo htmlspecialchars($log['reference_number']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClasses = [
                                        'sent' => 'badge-success',
                                        'failed' => 'badge-danger',
                                        'skipped' => 'badge-warning'
                                    ];
                                    $statusClass = $statusClasses[$log['status']] ?? 'badge-secondary';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($log['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($log['error_message']): ?>
                                        <button type="button" class="btn btn-sm btn-outline" onclick="showError('<?php echo htmlspecialchars(addslashes($log['error_message']), ENT_QUOTES); ?>')" title="View Error">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-wrapper">
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $page - 1])); ?>" class="page-btn">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $i])); ?>" 
                               class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $page + 1])); ?>" class="page-btn">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> Error Details</h3>
            <button type="button" class="close-btn" onclick="closeErrorModal()">&times;</button>
        </div>
        <div class="modal-body">
            <pre id="errorContent" style="background: #f8fafc; padding: 15px; border-radius: 4px; white-space: pre-wrap; word-break: break-word;"></pre>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeErrorModal()">Close</button>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}
.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}
.stat-info {
    display: flex;
    flex-direction: column;
}
.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}
.stat-label {
    font-size: 0.875rem;
    color: #64748b;
}
.filter-form .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
}
.filter-group {
    flex: 1;
    min-width: 150px;
}
.filter-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: #64748b;
}
.filter-actions {
    display: flex;
    gap: 0.5rem;
}
.date-text {
    display: block;
    font-weight: 500;
}
.time-text {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
}
.email-text, .subject-text {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}
.ref-link {
    color: #1b4a9a;
    text-decoration: none;
    font-family: monospace;
}
.ref-link:hover {
    text-decoration: underline;
}
.badge-success { background: #d1fae5; color: #065f46; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-info { background: #1b4a9a; color: #ffffff; }

/* Modal styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow: auto;
}
.modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #64748b;
}
.modal-body {
    padding: 1.5rem;
}
.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    text-align: right;
}
</style>

<script>
function showError(message) {
    document.getElementById('errorContent').textContent = message;
    document.getElementById('errorModal').style.display = 'flex';
}

function closeErrorModal() {
    document.getElementById('errorModal').style.display = 'none';
}

// Close modal on click outside
document.getElementById('errorModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeErrorModal();
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
