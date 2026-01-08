<?php
/**
 * Admin Dashboard
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../models/ComplaintAdmin.php';
require_once __DIR__ . '/../models/ActivityLog.php';

$auth = auth();
$auth->requireLogin();

$complaintModel = new ComplaintAdmin();
$activityLog = new ActivityLog();

// Get statistics
$stats = $complaintModel->getStatistics();
$recentComplaints = $complaintModel->getRecent(8);
$recentActivity = $activityLog->getRecentActivity(10);

// Status config
$statusConfig = STATUS_CONFIG;

include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-grid">
    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card stat-total">
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($stats['total']); ?></span>
                <span class="stat-label">Total Complaints</span>
            </div>
        </div>
        
        <div class="stat-card stat-pending">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($stats['by_status']['pending'] ?? 0); ?></span>
                <span class="stat-label">Pending</span>
            </div>
        </div>
        
        <div class="stat-card stat-accepted">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($stats['by_status']['accepted'] ?? 0); ?></span>
                <span class="stat-label">Accepted</span>
            </div>
        </div>
        
        <div class="stat-card stat-progress">
            <div class="stat-icon"><i class="fas fa-spinner"></i></div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($stats['by_status']['in_progress'] ?? 0); ?></span>
                <span class="stat-label">In Progress</span>
            </div>
        </div>
        
        <div class="stat-card stat-resolved">
            <div class="stat-icon"><i class="fas fa-check-double"></i></div>
            <div class="stat-content">
                <span class="stat-value"><?php echo number_format($stats['by_status']['resolved'] ?? 0); ?></span>
                <span class="stat-label">Resolved</span>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-content">
        <!-- Recent Complaints -->
        <div class="dashboard-card recent-complaints" id="recent-complaints-card">
            <div class="card-header">
                <h2><i class="fas fa-clipboard-list"></i> Recent Complaints</h2>
                <a href="/SDO-cts/admin/complaints.php" class="btn btn-sm btn-outline">View All →</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentComplaints)): ?>
                <div class="empty-state">
                    <span class="empty-icon"><i class="fas fa-inbox"></i></span>
                    <p>No complaints yet</p>
                </div>
                <?php else: ?>
                <div class="complaints-list">
                    <?php foreach ($recentComplaints as $complaint): ?>
                    <a href="/SDO-cts/admin/complaint-view.php?id=<?php echo $complaint['id']; ?>" class="complaint-item">
                        <div class="complaint-info">
                            <span class="complaint-ref"><?php echo htmlspecialchars($complaint['reference_number']); ?></span>
                            <span class="complaint-name"><?php echo htmlspecialchars($complaint['name_pangalan']); ?></span>
                            <span class="complaint-preview"><?php echo htmlspecialchars(substr($complaint['narration_complaint'], 0, 80)); ?>...</span>
                        </div>
                        <div class="complaint-meta">
                            <span class="status-badge status-<?php echo $complaint['status']; ?>">
                                <?php echo $statusConfig[$complaint['status']]['icon'] . ' ' . $statusConfig[$complaint['status']]['label']; ?>
                            </span>
                            <span class="complaint-date"><?php echo date('M j, Y', strtotime($complaint['created_at'])); ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="dashboard-sidebar">
            <!-- Quick Stats -->
            <div class="dashboard-card quick-stats">
                <div class="card-header">
                    <h2><i class="fas fa-chart-pie"></i> Quick Stats</h2>
                </div>
                <div class="card-body">
                    <div class="quick-stat-item">
                        <span class="quick-stat-label">This Week</span>
                        <span class="quick-stat-value"><?php echo number_format($stats['this_week']); ?></span>
                    </div>
                    <div class="quick-stat-item">
                        <span class="quick-stat-label">This Month</span>
                        <span class="quick-stat-value"><?php echo number_format($stats['this_month']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-card recent-activity">
                <div class="card-header">
                    <h2><i class="fas fa-history"></i> Recent Activity</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($recentActivity)): ?>
                    <div class="empty-state small">
                        <p>No recent activity</p>
                    </div>
                    <?php else: ?>
                    <div class="activity-list">
                        <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <?php
                                $icons = [
                                    'login' => '<i class="fas fa-sign-in-alt"></i>',
                                    'logout' => '<i class="fas fa-sign-out-alt"></i>',
                                    'view' => '<i class="fas fa-eye"></i>',
                                    'create' => '<i class="fas fa-plus"></i>',
                                    'update' => '<i class="fas fa-edit"></i>',
                                    'delete' => '<i class="fas fa-trash"></i>',
                                    'status_change' => '<i class="fas fa-sync"></i>',
                                    'forward' => '<i class="fas fa-share"></i>',
                                    'accept' => '<i class="fas fa-check"></i>',
                                    'return' => '<i class="fas fa-undo"></i>'
                                ];
                                echo $icons[$activity['action_type']] ?? '<i class="fas fa-circle"></i>';
                                ?>
                            </div>
                            <div class="activity-content">
                                <span class="activity-user"><?php echo htmlspecialchars($activity['user_name'] ?? 'System'); ?></span>
                                <span class="activity-desc"><?php echo htmlspecialchars($activity['description'] ?? ucfirst($activity['action_type'])); ?></span>
                                <span class="activity-time"><?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard-specific: Auto-refresh recent complaints on new arrival
(function() {
    let lastTotal = <?php echo $stats['total']; ?>;
    const REFRESH_INTERVAL = 15000;
    
    function checkDashboardUpdates() {
        fetch('/SDO-cts/admin/api/notification-count.php', {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.counts.total > lastTotal) {
                // Highlight the recent complaints card
                const card = document.getElementById('recent-complaints-card');
                if (card) {
                    card.classList.add('card-highlight');
                    setTimeout(() => card.classList.remove('card-highlight'), 3000);
                }
                
                // Show refresh button on card header
                showDashboardRefresh(data.counts.total - lastTotal);
            }
        })
        .catch(error => console.log('Dashboard update check error:', error));
    }
    
    function showDashboardRefresh(newCount) {
        const cardHeader = document.querySelector('#recent-complaints-card .card-header');
        if (cardHeader && !cardHeader.querySelector('.refresh-link')) {
            const refreshLink = document.createElement('button');
            refreshLink.className = 'btn btn-sm btn-primary refresh-link';
            refreshLink.innerHTML = '<i class="fas fa-sync-alt"></i> ' + newCount + ' new';
            refreshLink.onclick = function() { location.reload(); };
            cardHeader.insertBefore(refreshLink, cardHeader.querySelector('a'));
        }
    }
    
    setInterval(checkDashboardUpdates, REFRESH_INTERVAL);
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

