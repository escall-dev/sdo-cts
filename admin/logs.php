<?php
/**
 * Activity Logs Page
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../models/AdminUser.php';

$auth = auth();
$auth->requirePermission('logs.view');

$activityLog = new ActivityLog();
$userModel = new AdminUser();

// Get filter parameters
$filters = [
    'user_id' => $_GET['user_id'] ?? '',
    'action_type' => $_GET['action_type'] ?? '',
    'entity_type' => $_GET['entity_type'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

// Get logs
$logs = $activityLog->getLogs($filters, $perPage, $offset);
$totalCount = $activityLog->getLogsCount($filters);
$totalPages = ceil($totalCount / $perPage);

// Get all users for filter dropdown
$allUsers = $userModel->getAll();

// Action types for filter
$actionTypes = [
    'login' => 'Login',
    'logout' => 'Logout',
    'view' => 'View',
    'create' => 'Create',
    'update' => 'Update',
    'delete' => 'Delete',
    'status_change' => 'Status Change',
    'forward' => 'Forward',
    'accept' => 'Accept',
    'return' => 'Return'
];

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <p class="page-subtitle">View all system activities and user actions</p>
    </div>
    <div class="page-header-right">
        <span class="result-count"><?php echo number_format($totalCount); ?> log entries</span>
    </div>
</div>

<!-- Filters -->
<div class="filter-bar">
    <form method="GET" action="" class="filter-form">
        <div class="filter-group">
            <label>User</label>
            <select name="user_id" class="filter-select">
                <option value="">All Users</option>
                <?php foreach ($allUsers as $user): ?>
                <option value="<?php echo $user['id']; ?>" <?php echo $filters['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Action Type</label>
            <select name="action_type" class="filter-select">
                <option value="">All Actions</option>
                <?php foreach ($actionTypes as $key => $label): ?>
                <option value="<?php echo $key; ?>" <?php echo $filters['action_type'] === $key ? 'selected' : ''; ?>>
                    <?php echo $label; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Entity Type</label>
            <select name="entity_type" class="filter-select">
                <option value="">All Types</option>
                <option value="complaint" <?php echo $filters['entity_type'] === 'complaint' ? 'selected' : ''; ?>>Complaint</option>
                <option value="user" <?php echo $filters['entity_type'] === 'user' ? 'selected' : ''; ?>>User</option>
                <option value="auth" <?php echo $filters['entity_type'] === 'auth' ? 'selected' : ''; ?>>Authentication</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Date From</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>" class="filter-input">
        </div>
        
        <div class="filter-group">
            <label>Date To</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to']); ?>" class="filter-input">
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
            <a href="/CTS/admin/logs.php" class="btn btn-outline btn-sm">Clear</a>
        </div>
    </form>
</div>

<!-- Logs Table -->
<div class="data-card">
    <?php if (empty($logs)): ?>
    <div class="empty-state">
        <span class="empty-icon"><i class="fas fa-history"></i></span>
        <h3>No logs found</h3>
        <p>Try adjusting your filters</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td>
                        <div class="cell-primary"><?php echo date('M j, Y', strtotime($log['created_at'])); ?></div>
                        <div class="cell-secondary"><?php echo date('g:i:s A', strtotime($log['created_at'])); ?></div>
                    </td>
                    <td>
                        <div class="cell-primary"><?php echo htmlspecialchars($log['user_name'] ?? 'System'); ?></div>
                        <div class="cell-secondary"><?php echo htmlspecialchars($log['user_email'] ?? ''); ?></div>
                    </td>
                    <td>
                        <span class="action-badge action-<?php echo $log['action_type']; ?>">
                            <?php echo $actionTypes[$log['action_type']] ?? ucfirst($log['action_type']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="entity-type"><?php echo ucfirst($log['entity_type']); ?></span>
                        <?php if ($log['entity_id']): ?>
                        <span class="entity-id">#<?php echo $log['entity_id']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="log-description"><?php echo htmlspecialchars($log['description'] ?? '-'); ?></div>
                    </td>
                    <td>
                        <code class="ip-address"><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></code>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <div class="pagination-info">
            Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $perPage, $totalCount); ?> of <?php echo $totalCount; ?>
        </div>
        <div class="pagination-links">
            <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $page - 1])); ?>" class="page-link">← Previous</a>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
            <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $i])); ?>" 
               class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $page + 1])); ?>" class="page-link">Next →</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

