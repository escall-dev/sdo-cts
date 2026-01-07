<?php
/**
 * User Management Page
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 * Access restricted to Super Admin only
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../models/AdminUser.php';

$auth = auth();
$auth->requireLogin();

// Only Super Admin can access User Management
if (!$auth->isSuperAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    include __DIR__ . '/403.php';
    exit;
}

$userModel = new AdminUser();

// Get filter parameters
$filters = [
    'role_id' => $_GET['role_id'] ?? '',
    'is_active' => isset($_GET['is_active']) ? $_GET['is_active'] : '',
    'search' => $_GET['search'] ?? ''
];

// Get users
$users = $userModel->getAll($filters);
$roles = $userModel->getRoles();

// Create role lookup
$rolesLookup = [];
foreach ($roles as $role) {
    $rolesLookup[$role['id']] = $role;
}

$units = UNITS;

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="page-header-left">
        <p class="page-subtitle">Manage admin users and their access levels</p>
    </div>
    <div class="page-header-right">
        <?php if ($auth->isSuperAdmin()): ?>
        <button type="button" class="btn btn-primary" onclick="openUserModal()">
            <i class="fas fa-user-plus"></i> Add User
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="filter-bar">
    <form method="GET" action="" class="filter-form">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" 
                   placeholder="Name or email..." class="filter-input">
        </div>
        
        <div class="filter-group">
            <label>Role</label>
            <select name="role_id" class="filter-select">
                <option value="">All Roles</option>
                <?php foreach ($roles as $role): ?>
                <option value="<?php echo $role['id']; ?>" <?php echo $filters['role_id'] == $role['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($role['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Status</label>
            <select name="is_active" class="filter-select">
                <option value="">All Status</option>
                <option value="1" <?php echo $filters['is_active'] === '1' ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo $filters['is_active'] === '0' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
            <a href="/SDO-cts/admin/users.php" class="btn btn-outline btn-sm">Clear</a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="data-card">
    <?php if (empty($users)): ?>
    <div class="empty-state">
        <span class="empty-icon"><i class="fas fa-users"></i></span>
        <h3>No users found</h3>
        <p>Try adjusting your filters or add a new user</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div class="user-cell">
                            <?php if (!empty($user['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Avatar" class="user-avatar-sm">
                            <?php else: ?>
                            <div class="user-avatar-placeholder-sm">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <?php endif; ?>
                            <div>
                                <div class="cell-primary"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                <div class="cell-secondary"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="role-badge role-<?php echo strtolower(str_replace(' ', '-', $user['role_name'])); ?>">
                            <?php echo htmlspecialchars($user['role_name']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($user['unit'] ?? '-'); ?></td>
                    <td>
                        <?php if ($user['is_active']): ?>
                        <span class="status-badge status-active">Active</span>
                        <?php else: ?>
                        <span class="status-badge status-inactive">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?>
                    </td>
                    <td>
                        <div class="cell-primary"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                        <?php if ($user['created_by_name']): ?>
                        <div class="cell-secondary">by <?php echo htmlspecialchars($user['created_by_name']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($auth->isSuperAdmin()): ?>
                            <button type="button" class="btn btn-sm btn-outline" 
                                    onclick='openEditModal(<?php echo json_encode($user); ?>)'
                                    title="Edit User">Edit</button>
                            
                            <?php if ($user['id'] !== $auth->getUserId()): ?>
                                <?php if ($user['is_active']): ?>
                                <form method="POST" action="/SDO-cts/admin/api/user-status.php" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="deactivate">
                                    <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline" title="Deactivate" 
                                            onclick="return confirm('Deactivate this user?')">Deactivate</button>
                                </form>
                                <?php else: ?>
                                <form method="POST" action="/SDO-cts/admin/api/user-status.php" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="activate">
                                    <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline" title="Activate">Activate</button>
                                </form>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php if ($auth->isSuperAdmin()): ?>
<!-- Add/Edit User Modal -->
<div class="modal-overlay" id="userModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 id="userModalTitle">Add User</h3>
            <button type="button" class="modal-close" onclick="closeModal('userModal')">&times;</button>
        </div>
        <form method="POST" action="/SDO-cts/admin/api/save-user.php" id="userForm">
            <div class="modal-body">
                <input type="hidden" name="user_id" id="userId" value="">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="full_name" id="userFullName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" id="userEmail" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Role <span class="required">*</span></label>
                        <select name="role_id" id="userRole" class="form-control" required>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit/Section</label>
                        <select name="unit" id="userUnit" class="form-control">
                            <option value="">-- Select Unit --</option>
                            <?php foreach ($units as $key => $name): ?>
                            <option value="<?php echo $key; ?>"><?php echo $key; ?> - <?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password <span id="passwordReq" class="required">*</span></label>
                        <input type="password" name="password" id="userPassword" class="form-control" minlength="8">
                        <small class="form-hint" id="passwordHint">Minimum 8 characters. Leave blank to keep current password when editing.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirm" id="userPasswordConfirm" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" id="userActive" value="1" checked>
                        User is active
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('userModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="userSubmitBtn">Save User</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUserModal() {
    document.getElementById('userModalTitle').textContent = 'Add User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordReq').style.display = 'inline';
    document.getElementById('passwordHint').textContent = 'Minimum 8 characters';
    document.getElementById('userActive').checked = true;
    document.getElementById('userModal').classList.add('active');
}

function openEditModal(user) {
    document.getElementById('userModalTitle').textContent = 'Edit User';
    document.getElementById('userId').value = user.id;
    document.getElementById('userFullName').value = user.full_name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userRole').value = user.role_id;
    document.getElementById('userUnit').value = user.unit || '';
    document.getElementById('userActive').checked = user.is_active == 1;
    document.getElementById('userPassword').value = '';
    document.getElementById('userPasswordConfirm').value = '';
    document.getElementById('userPassword').required = false;
    document.getElementById('passwordReq').style.display = 'none';
    document.getElementById('passwordHint').textContent = 'Leave blank to keep current password';
    document.getElementById('userModal').classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Password confirmation validation
document.getElementById('userForm').addEventListener('submit', function(e) {
    const password = document.getElementById('userPassword').value;
    const confirm = document.getElementById('userPasswordConfirm').value;
    
    if (password && password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
});

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('active');
        }
    });
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

