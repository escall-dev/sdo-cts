<?php
/**
 * User Profile Page
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../models/AdminUser.php';

$auth = auth();
$auth->requireLogin();

$userModel = new AdminUser();
$user = $auth->getUser();

$success = '';
$error = '';
$avatarSuccess = '';
$avatarError = '';

// Create avatars directory if it doesn't exist
$avatarDir = __DIR__ . '/../uploads/avatars/';
if (!is_dir($avatarDir)) {
    mkdir($avatarDir, 0755, true);
}

// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_avatar') {
    if (!$auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $avatarError = 'Invalid security token. Please try again.';
    } elseif (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $avatarError = 'Please select an image to upload.';
    } else {
        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $avatarError = 'Invalid file type. Please upload a JPEG, PNG, GIF, or WebP image.';
        } elseif ($file['size'] > $maxSize) {
            $avatarError = 'File too large. Maximum size is 5MB.';
        } else {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . strtolower($extension);
            $filepath = $avatarDir . $filename;
            
            // Delete old avatar if exists and is a local file
            if (!empty($user['avatar_url']) && strpos($user['avatar_url'], '/SDO-cts/uploads/avatars/') !== false) {
                $oldFilename = basename($user['avatar_url']);
                $oldFilepath = $avatarDir . $oldFilename;
                if (file_exists($oldFilepath)) {
                    unlink($oldFilepath);
                }
            }
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $avatarUrl = '/SDO-cts/uploads/avatars/' . $filename;
                $userModel->update($user['id'], ['avatar_url' => $avatarUrl]);
                $auth->logActivity('update', 'user', $user['id'], 'Changed profile avatar');
                
                // Refresh user data
                $user = $userModel->getById($user['id']);
                $_SESSION[ADMIN_SESSION_NAME]['avatar_url'] = $avatarUrl;
                
                $avatarSuccess = 'Avatar updated successfully!';
            } else {
                $avatarError = 'Failed to upload avatar. Please try again.';
            }
        }
    }
}

// Handle avatar removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_avatar') {
    if (!$auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $avatarError = 'Invalid security token. Please try again.';
    } else {
        // Delete old avatar file if it's a local file
        if (!empty($user['avatar_url']) && strpos($user['avatar_url'], '/SDO-cts/uploads/avatars/') !== false) {
            $oldFilename = basename($user['avatar_url']);
            $oldFilepath = $avatarDir . $oldFilename;
            if (file_exists($oldFilepath)) {
                unlink($oldFilepath);
            }
        }
        
        $userModel->update($user['id'], ['avatar_url' => null]);
        $auth->logActivity('update', 'user', $user['id'], 'Removed profile avatar');
        
        // Refresh user data
        $user = $userModel->getById($user['id']);
        $_SESSION[ADMIN_SESSION_NAME]['avatar_url'] = null;
        
        $avatarSuccess = 'Avatar removed successfully!';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!$auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate current password
        if (!$userModel->authenticate($user['email'], $currentPassword)) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } else {
            try {
                $userModel->update($user['id'], ['password' => $newPassword]);
                $auth->logActivity('update', 'user', $user['id'], 'Changed own password');
                $success = 'Password changed successfully.';
            } catch (Exception $e) {
                $error = 'Failed to change password. Please try again.';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="profile-container">
    <!-- Profile Card -->
    <div class="profile-card">
        <div class="profile-header">
            <div class="avatar-section">
                <?php if (!empty($user['avatar_url'])): ?>
                <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Avatar" class="profile-avatar" id="currentAvatar">
                <?php else: ?>
                <div class="profile-avatar-placeholder" id="avatarPlaceholder">
                    <?php echo strtoupper(substr($user['full_name'], 0, 2)); ?>
                </div>
                <?php endif; ?>
                <button type="button" class="avatar-edit-btn" onclick="document.getElementById('avatarModal').classList.add('show')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                </button>
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                <span class="role-badge"><?php echo htmlspecialchars($user['role_name']); ?></span>
            </div>
        </div>
        
        <div class="profile-details">
            <div class="detail-row">
                <span class="detail-label">Unit/Section</span>
                <span class="detail-value"><?php echo htmlspecialchars($user['unit'] ?? 'Not assigned'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last Login</span>
                <span class="detail-value">
                    <?php echo $user['last_login'] ? date('F j, Y \a\t g:i A', strtotime($user['last_login'])) : 'First login'; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Account Created</span>
                <span class="detail-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
            </div>
        </div>
        
        <?php if ($avatarSuccess): ?>
        <div class="alert alert-success" style="margin-top: 16px;"><?php echo htmlspecialchars($avatarSuccess); ?></div>
        <?php endif; ?>
        
        <?php if ($avatarError): ?>
        <div class="alert alert-error" style="margin-top: 16px;"><?php echo htmlspecialchars($avatarError); ?></div>
        <?php endif; ?>
    </div>

    <!-- Change Password Card -->
    <div class="settings-card">
        <h3><i class="fas fa-lock"></i> Change Password</h3>
        
        <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($user['password_hash']): ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" minlength="8" required>
                <small class="form-hint">Minimum 8 characters</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
        <?php else: ?>
        <div class="info-box">
            <p>You logged in with Google. Password changes are managed through your Google account.</p>
            <a href="https://myaccount.google.com/security" target="_blank" class="btn btn-outline">
                Manage Google Account →
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.profile-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    max-width: 1000px;
}

.profile-card, .settings-card {
    background: var(--card-bg);
    border-radius: var(--radius-lg);
    padding: 32px;
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 24px;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-avatar-placeholder {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: var(--primary-gradient);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
}

.profile-info h2 {
    margin: 0 0 4px;
    color: var(--text-primary);
}

.profile-email {
    color: var(--text-muted);
    margin: 0 0 12px;
}

.profile-details {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-light);
}

.detail-label {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.detail-value {
    color: var(--text-primary);
    font-weight: 500;
}

.settings-card h3 {
    margin: 0 0 24px;
    color: var(--text-primary);
}

.info-box {
    background: var(--bg-tertiary);
    padding: 20px;
    border-radius: var(--radius-md);
    text-align: center;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
}

.info-box p {
    margin: 0 0 16px;
    color: var(--text-secondary);
}

@media (max-width: 768px) {
    .profile-container {
        grid-template-columns: 1fr;
    }
    
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .avatar-section {
        margin: 0 auto;
    }
}

/* Avatar Section Styles */
.avatar-section {
    position: relative;
    display: inline-block;
}

.avatar-edit-btn {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border: 2px solid var(--card-bg);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.avatar-edit-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Avatar Modal */
.avatar-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.avatar-modal.show {
    display: flex;
}

.avatar-modal-content {
    background: var(--card-bg);
    border-radius: var(--radius-lg);
    padding: 32px;
    max-width: 450px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.avatar-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.avatar-modal-header h3 {
    margin: 0;
    color: var(--text-primary);
}

.modal-close-btn {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s;
}

.modal-close-btn:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

.avatar-preview-section {
    text-align: center;
    margin-bottom: 24px;
}

.avatar-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 16px;
    display: block;
    border: 4px solid var(--border-color);
}

.avatar-preview-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: var(--primary-gradient);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 700;
    margin: 0 auto 16px;
    border: 4px solid var(--border-color);
}

.upload-zone {
    border: 2px dashed var(--border-color);
    border-radius: var(--radius-md);
    padding: 32px 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: var(--bg-tertiary);
    margin-bottom: 16px;
}

.upload-zone:hover {
    border-color: var(--primary);
    background: rgba(59, 130, 246, 0.05);
}

.upload-zone.dragover {
    border-color: var(--primary);
    background: rgba(59, 130, 246, 0.1);
}

.upload-zone svg {
    width: 48px;
    height: 48px;
    color: var(--text-muted);
    margin-bottom: 12px;
}

.upload-zone p {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.upload-zone .upload-hint {
    font-size: 0.8rem;
    margin-top: 8px;
    color: var(--text-muted);
    opacity: 0.7;
}

.avatar-file-input {
    display: none;
}

.avatar-modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}

.avatar-modal-actions .btn {
    flex: 1;
}

.btn-danger-outline {
    background: transparent;
    color: var(--danger);
    border: 1px solid var(--danger);
}

.btn-danger-outline:hover {
    background: var(--danger);
    color: white;
}
</style>

<!-- Avatar Upload Modal -->
<div class="avatar-modal" id="avatarModal">
    <div class="avatar-modal-content">
        <div class="avatar-modal-header">
            <h3><i class="fas fa-camera"></i> Change Profile Photo</h3>
            <button type="button" class="modal-close-btn" onclick="document.getElementById('avatarModal').classList.remove('show')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <div class="avatar-preview-section">
            <?php if (!empty($user['avatar_url'])): ?>
            <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Current Avatar" class="avatar-preview" id="avatarPreview">
            <?php else: ?>
            <div class="avatar-preview-placeholder" id="avatarPreviewPlaceholder">
                <?php echo strtoupper(substr($user['full_name'], 0, 2)); ?>
            </div>
            <?php endif; ?>
        </div>
        
        <form method="POST" action="" enctype="multipart/form-data" id="avatarForm">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="upload_avatar">
            
            <div class="upload-zone" id="uploadZone">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                <p>Click or drag image here to upload</p>
                <p class="upload-hint">JPEG, PNG, GIF, WebP • Max 5MB</p>
            </div>
            <input type="file" name="avatar" id="avatarInput" class="avatar-file-input" accept="image/jpeg,image/png,image/gif,image/webp">
            
            <div class="avatar-modal-actions">
                <?php if (!empty($user['avatar_url'])): ?>
                <button type="button" class="btn btn-danger-outline" onclick="removeAvatar()">Remove</button>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>Upload Photo</button>
            </div>
        </form>
        
        <!-- Hidden form for avatar removal -->
        <form method="POST" action="" id="removeAvatarForm" style="display: none;">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="remove_avatar">
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('uploadZone');
    const avatarInput = document.getElementById('avatarInput');
    const avatarForm = document.getElementById('avatarForm');
    const uploadBtn = document.getElementById('uploadBtn');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarPreviewPlaceholder = document.getElementById('avatarPreviewPlaceholder');
    
    // Click to upload
    uploadZone.addEventListener('click', () => avatarInput.click());
    
    // Drag and drop
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            avatarInput.files = e.dataTransfer.files;
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });
    
    // File input change
    avatarInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    function handleFileSelect(file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPEG, PNG, GIF, or WebP)');
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File is too large. Maximum size is 5MB.');
            return;
        }
        
        // Preview the image
        const reader = new FileReader();
        reader.onload = function(e) {
            if (avatarPreview) {
                avatarPreview.src = e.target.result;
            } else if (avatarPreviewPlaceholder) {
                // Replace placeholder with image
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'avatar-preview';
                img.id = 'avatarPreview';
                avatarPreviewPlaceholder.parentNode.replaceChild(img, avatarPreviewPlaceholder);
            }
            
            // Enable upload button
            uploadBtn.disabled = false;
            uploadBtn.textContent = 'Upload Photo';
        };
        reader.readAsDataURL(file);
    }
    
    // Close modal on outside click
    document.getElementById('avatarModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
        }
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('avatarModal').classList.remove('show');
        }
    });
});

function removeAvatar() {
    if (confirm('Are you sure you want to remove your profile photo?')) {
        document.getElementById('removeAvatarForm').submit();
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

