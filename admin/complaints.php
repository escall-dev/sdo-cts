<?php
/**
 * Complaint Management Page
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../models/ComplaintAdmin.php';

$auth = auth();
$auth->requirePermission('complaints.view');

$complaintModel = new ComplaintAdmin();

// Get filter parameters
$filters = [
    'status' => $_GET['status'] ?? '',
    'referred_to' => $_GET['referred_to'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;

// Get complaints
$complaints = $complaintModel->getAll($filters, $page, $perPage);
$totalCount = $complaintModel->getCount($filters);
$totalPages = ceil($totalCount / $perPage);

// Status config
$statusConfig = STATUS_CONFIG;
$units = UNITS;

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <p class="page-subtitle">Manage and track all complaint records</p>
    </div>
    <div class="page-header-right">
        <span class="result-count"><?php echo number_format($totalCount); ?> complaint<?php echo $totalCount !== 1 ? 's' : ''; ?></span>
    </div>
</div>

<!-- Filters -->
<div class="filter-bar">
    <form method="GET" action="" class="filter-form" id="filterForm">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" 
                   placeholder="Reference, name, email..." class="filter-input">
        </div>
        
        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <?php foreach ($statusConfig as $key => $config): ?>
                <option value="<?php echo $key; ?>" <?php echo $filters['status'] === $key ? 'selected' : ''; ?>>
                    <?php echo $config['label']; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Referred To</label>
            <select name="referred_to" class="filter-select">
                <option value="">All Units</option>
                <?php foreach ($units as $key => $name): ?>
                <option value="<?php echo $key; ?>" <?php echo $filters['referred_to'] === $key ? 'selected' : ''; ?>>
                    <?php echo $key; ?>
                </option>
                <?php endforeach; ?>
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
            <a href="/SDO-cts/admin/complaints.php" class="btn btn-outline btn-sm">Clear</a>
        </div>
    </form>
</div>

<!-- Complaints Table -->
<div class="data-card">
    <?php if (empty($complaints)): ?>
    <div class="empty-state">
        <h3>No complaints found</h3>
        <p>Try adjusting your filters or search criteria</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Complainant</th>
                    <th>Subject/Involved</th>
                   
                    <th>Status</th>
                    <th>Date Filed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $complaint): ?>
                <?php
                    $isUploadedForm   = ($complaint['signature_type'] ?? '') === 'uploaded_form';
                    $complainantName  = $complaint['name_pangalan'] ?? '';
                    $complainantEmail = $complaint['email_address'] ?? '';
                    $involvedName     = $complaint['involved_full_name'] ?? '';
                    $docCount         = isset($complaint['doc_count']) ? (int)$complaint['doc_count'] : 0;

                    // Fallback detection for older uploaded-form submissions:
                    // if core fields are blank and there are documents, mark as uploaded.
                    $blankCore = 
                        empty(trim($complainantName)) &&
                        empty(trim($complaint['address_tirahan'] ?? '')) &&
                        empty(trim($complaint['contact_number'] ?? '')) &&
                        empty(trim($complainantEmail)) &&
                        empty(trim($complaint['narration_complaint'] ?? '')) &&
                        empty(trim($involvedName));

                    if ($blankCore && $docCount > 0) {
                        $isUploadedForm = true;
                    }

                    if ($isUploadedForm) {
                        $complainantName = 'Document uploaded';
                        $involvedName = 'Document uploaded';
                        $complainantEmail = '';
                    }
                ?>
                <tr>
                    <td>
                        <a href="/SDO-cts/admin/complaint-view.php?id=<?php echo $complaint['id']; ?>" class="ref-link">
                            <?php echo htmlspecialchars($complaint['reference_number']); ?>
                        </a>
                    </td>
                    <td>
                        <div class="cell-primary"><?php echo htmlspecialchars($complainantName); ?></div>
                        <?php if ($complainantEmail): ?>
                        <div class="cell-secondary"><?php echo htmlspecialchars($complainantEmail); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="cell-primary"><?php echo htmlspecialchars($involvedName); ?></div>
                    </td>
                   
                    <td>
                        <span class="status-badge status-<?php echo $complaint['status']; ?>">
                            <?php echo $statusConfig[$complaint['status']]['icon'] . ' ' . $statusConfig[$complaint['status']]['label']; ?>
                        </span>
                    </td>
                    <td>
                        <div class="cell-primary"><?php echo date('M j, Y', strtotime($complaint['date_petsa'])); ?></div>
                        <div class="cell-secondary"><?php echo date('g:i A', strtotime($complaint['date_petsa'])); ?></div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="/SDO-cts/admin/complaint-view.php?id=<?php echo $complaint['id']; ?>" 
                               class="btn btn-sm btn-outline" title="View Details">View</a>
                            <?php if ($auth->hasPermission('complaints.update')): ?>
                            <button type="button" class="btn btn-sm btn-outline" 
                                    onclick="openStatusModal(<?php echo $complaint['id']; ?>, '<?php echo $complaint['status']; ?>')"
                                    title="Update Status">Edit</button>
                            <?php endif; ?>
                        </div>
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
            Showing <?php echo (($page - 1) * $perPage) + 1; ?> - <?php echo min($page * $perPage, $totalCount); ?> of <?php echo $totalCount; ?>
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

<!-- Status Update Modal -->
<div class="modal-overlay" id="statusModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Update Status</h3>
            <button type="button" class="modal-close" onclick="closeModal('statusModal')">&times;</button>
        </div>
        <form method="POST" action="/SDO-cts/admin/api/update-status.php" id="statusForm">
            <div class="modal-body">
                <input type="hidden" name="complaint_id" id="statusComplaintId">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                
                <div class="form-group">
                    <label class="form-label">New Status</label>
                    <select name="status" id="statusSelect" class="form-control" required>
                        <!-- Options populated by JavaScript -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Add notes about this status change..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('statusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<script>
const statusConfig = <?php echo json_encode($statusConfig); ?>;
const statusWorkflow = <?php echo json_encode(STATUS_WORKFLOW); ?>;

function openStatusModal(complaintId, currentStatus) {
    document.getElementById('statusComplaintId').value = complaintId;
    
    const select = document.getElementById('statusSelect');
    select.innerHTML = '';
    
    const allowedStatuses = statusWorkflow[currentStatus] || [];
    allowedStatuses.forEach(status => {
        const option = document.createElement('option');
        option.value = status;
        // Only show label - HTML icons don't render in select options
        option.textContent = statusConfig[status].label;
        select.appendChild(option);
    });
    
    if (allowedStatuses.length === 0) {
        select.innerHTML = '<option value="">No transitions available</option>';
    }
    
    document.getElementById('statusModal').classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('active');
        }
    });
});

// Auto-refresh complaints table when new complaints arrive
(function() {
    let lastTotalCount = <?php echo $totalCount; ?>;
    const REFRESH_INTERVAL = 15000; // Check every 15 seconds
    
    function checkForNewComplaints() {
        fetch('/SDO-cts/admin/api/notification-count.php', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.counts.total > lastTotalCount) {
                // New complaints detected - show refresh prompt
                showRefreshPrompt(data.counts.total - lastTotalCount);
            }
        })
        .catch(error => console.log('Check error:', error));
    }
    
    function showRefreshPrompt(newCount) {
        // Check if prompt already exists
        if (document.querySelector('.refresh-prompt')) return;
        
        const prompt = document.createElement('div');
        prompt.className = 'refresh-prompt';
        prompt.innerHTML = `
            <i class="fas fa-sync-alt"></i>
            <span>${newCount} new complaint${newCount > 1 ? 's' : ''} received</span>
            <button onclick="location.reload()" class="btn btn-sm btn-primary">Refresh</button>
            <button onclick="this.parentElement.remove()" class="btn btn-sm btn-outline">Dismiss</button>
        `;
        
        const pageHeader = document.querySelector('.page-header');
        if (pageHeader) {
            pageHeader.parentNode.insertBefore(prompt, pageHeader.nextSibling);
        }
    }
    
    // Start checking
    setInterval(checkForNewComplaints, REFRESH_INTERVAL);
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

