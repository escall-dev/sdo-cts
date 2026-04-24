<?php
/**
 * Email Settings & Test Page
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/../services/email/EmailService.php';

$auth = auth();

// Check authentication
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Only admins can access email settings
if (!$auth->hasPermission('settings.view')) {
    header('Location: 403.php');
    exit;
}

$testResult = null;
$connectionResult = null;

// Handle test email send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
    if (!$auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $testResult = ['success' => false, 'message' => 'Invalid security token.'];
    } else {
        $testEmail = trim($_POST['test_email'] ?? '');
        
        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $testResult = ['success' => false, 'message' => 'Please enter a valid email address.'];
        } else {
            $emailService = new EmailService();
            
            $subject = "SDO CTS - Test Email";
            $body = '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #f8fafc; padding: 30px; border-radius: 8px;">
        <h2 style="color: #1b4a9a;">Email Configuration Test</h2>
        <p>This is a test email from the SDO CTS email notification system.</p>
        <div style="background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #065f46;"><strong>✓ Success!</strong></p>
            <p style="margin: 5px 0 0 0; color: #065f46;">Your email configuration is working correctly.</p>
        </div>
        <p><strong>Test Details:</strong></p>
        <ul style="color: #475569;">
            <li>Sent at: ' . date('F j, Y \a\t g:i:s A') . '</li>
            <li>SMTP Host: ' . htmlspecialchars(SMTP_HOST) . '</li>
            <li>From: ' . htmlspecialchars(MAIL_FROM_ADDRESS ?: 'Not configured') . '</li>
        </ul>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        <p style="font-size: 12px; color: #64748b;">SDO CTS - San Pedro Division Office Complaint Tracking System</p>
    </div>
</body>
</html>';

            $result = $emailService->send($testEmail, $subject, $body, 'test_email');
            
            if ($result) {
                $testResult = ['success' => true, 'message' => 'Test email sent successfully to ' . $testEmail];
                $auth->logActivity('email_test', 'system', null, 'Test email sent to ' . $testEmail);
            } else {
                $testResult = ['success' => false, 'message' => 'Failed to send test email. Error: ' . $emailService->getLastError()];
            }
        }
    }
}

// Handle connection test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_connection'])) {
    if ($auth->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $emailService = new EmailService();
        $connectionResult = $emailService->testConnection();
    }
}

// Include header
include __DIR__ . '/includes/header.php';
?>

<div class="content-header">
    <div class="header-left">
        <h1><i class="fas fa-cog"></i> Email Settings</h1>
        <p class="subtitle">Configure and test email notifications</p>
    </div>
    <div class="header-actions">
        <a href="email-logs.php" class="btn btn-outline">
            <i class="fas fa-history"></i> View Email Logs
        </a>
    </div>
</div>

<?php if ($testResult): ?>
    <div class="alert <?php echo $testResult['success'] ? 'alert-success' : 'alert-danger'; ?>">
        <i class="fas <?php echo $testResult['success'] ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($testResult['message']); ?>
    </div>
<?php endif; ?>

<?php if ($connectionResult): ?>
    <div class="alert <?php echo $connectionResult['success'] ? 'alert-success' : 'alert-danger'; ?>">
        <i class="fas <?php echo $connectionResult['success'] ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($connectionResult['message']); ?>
    </div>
<?php endif; ?>

<div class="settings-grid">
    <!-- Current Configuration -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> Current Configuration</h3>
        </div>
        <div class="card-body">
            <table class="config-table">
                <tr>
                    <td><strong>Email Notifications</strong></td>
                    <td>
                        <span class="badge <?php echo MAIL_ENABLED ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo MAIL_ENABLED ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>SMTP Host</strong></td>
                    <td><code><?php echo htmlspecialchars(SMTP_HOST); ?></code></td>
                </tr>
                <tr>
                    <td><strong>SMTP Port</strong></td>
                    <td><code><?php echo SMTP_PORT; ?></code></td>
                </tr>
                <tr>
                    <td><strong>Encryption</strong></td>
                    <td><code><?php echo strtoupper(SMTP_ENCRYPTION); ?></code></td>
                </tr>
                <tr>
                    <td><strong>SMTP Auth</strong></td>
                    <td>
                        <span class="badge <?php echo SMTP_AUTH ? 'badge-success' : 'badge-warning'; ?>">
                            <?php echo SMTP_AUTH ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>SMTP Username</strong></td>
                    <td><code><?php echo SMTP_USERNAME ? htmlspecialchars(SMTP_USERNAME) : '<em>Not set</em>'; ?></code></td>
                </tr>
                <tr>
                    <td><strong>SMTP Password</strong></td>
                    <td><code><?php echo SMTP_PASSWORD ? '••••••••' : '<em>Not set</em>'; ?></code></td>
                </tr>
                <tr>
                    <td><strong>From Address</strong></td>
                    <td><code><?php echo MAIL_FROM_ADDRESS ? htmlspecialchars(MAIL_FROM_ADDRESS) : '<em>Not set</em>'; ?></code></td>
                </tr>
                <tr>
                    <td><strong>From Name</strong></td>
                    <td><?php echo htmlspecialchars(MAIL_FROM_NAME); ?></td>
                </tr>
                <tr>
                    <td><strong>Reply-To</strong></td>
                    <td><code><?php echo MAIL_REPLY_TO ? htmlspecialchars(MAIL_REPLY_TO) : '<em>Same as From</em>'; ?></code></td>
                </tr>
                <tr>
                    <td><strong>Admin Recipients</strong></td>
                    <td><code><?php echo ADMIN_EMAIL_RECIPIENTS ? htmlspecialchars(ADMIN_EMAIL_RECIPIENTS) : '<em>Not set</em>'; ?></code></td>
                </tr>
            </table>

            <form method="POST" style="margin-top: 1.5rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                <button type="submit" name="test_connection" class="btn btn-outline">
                    <i class="fas fa-plug"></i> Test SMTP Connection
                </button>
            </form>
        </div>
    </div>

    <!-- Send Test Email -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-paper-plane"></i> Send Test Email</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                
                <div class="form-group">
                    <label for="test_email">Recipient Email Address</label>
                    <input type="email" id="test_email" name="test_email" class="form-control" 
                           placeholder="Enter email to send test" required
                           value="<?php echo htmlspecialchars($_POST['test_email'] ?? 'ict.sanpedrocity@deped.gov.ph'); ?>">
                    <small class="form-help">A test email will be sent to this address to verify the configuration.</small>
                </div>

                <button type="submit" name="send_test" class="btn btn-primary" <?php echo !MAIL_ENABLED ? 'disabled' : ''; ?>>
                    <i class="fas fa-paper-plane"></i> Send Test Email
                </button>

                <?php if (!MAIL_ENABLED): ?>
                    <p class="text-danger" style="margin-top: 1rem;">
                        <i class="fas fa-exclamation-triangle"></i> Email notifications are currently disabled.
                    </p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Configuration Instructions -->
    <div class="card" style="grid-column: 1 / -1;">
        <div class="card-header">
            <h3><i class="fas fa-book"></i> Configuration Instructions</h3>
        </div>
        <div class="card-body">
            <div class="instructions">
                <h4>How to Configure Email Settings</h4>
                <p>Email settings are configured via environment variables in the <code>.env</code> file located in the project root directory.</p>
                
                <h5>For Gmail / Google Workspace:</h5>
                <ol>
                    <li>Enable 2-Factor Authentication on your Google Account</li>
                    <li>Generate an App Password at <a href="https://myaccount.google.com/apppasswords" target="_blank">myaccount.google.com/apppasswords</a></li>
                    <li>Use these settings:
                        <pre>SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password</pre>
                    </li>
                </ol>

                <h5>For Custom SMTP Server:</h5>
                <ol>
                    <li>Get your SMTP credentials from your email provider or IT administrator</li>
                    <li>Update the .env file with your server details:
                        <pre>SMTP_HOST=your-smtp-server.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USERNAME=your-username
SMTP_PASSWORD=your-password</pre>
                    </li>
                </ol>

                <h5>Setting the Sender Email:</h5>
                <p>To set the "From" address that recipients will see:</p>
                <pre>MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME=SDO CTS - San Pedro Division Office</pre>

                <h5>Admin Notification Recipients:</h5>
                <p>Set comma-separated email addresses to receive notifications for new complaints:</p>
                <pre>ADMIN_EMAIL_RECIPIENTS=admin@domain.com,supervisor@domain.com</pre>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> After modifying the .env file, refresh this page to see the updated configuration.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
}
.config-table {
    width: 100%;
    border-collapse: collapse;
}
.config-table td {
    padding: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
}
.config-table td:first-child {
    width: 40%;
    color: #64748b;
}
.config-table code {
    background: #f1f5f9;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
}
.form-group {
    margin-bottom: 1.5rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}
.form-help {
    display: block;
    margin-top: 0.5rem;
    color: #64748b;
    font-size: 0.875rem;
}
.instructions h4 {
    color: #1b4a9a;
    margin-bottom: 1rem;
}
.instructions h5 {
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    color: #334155;
}
.instructions ol {
    padding-left: 1.5rem;
    color: #475569;
}
.instructions li {
    margin-bottom: 0.5rem;
}
.instructions pre {
    background: #f1f5f9;
    padding: 1rem;
    border-radius: 6px;
    overflow-x: hidden;
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 0.875rem;
    margin: 0.5rem 0;
}
.alert {
    padding: 1rem 1.25rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}
.alert-success {
    background: #d1fae5;
    color: #065f46;
}
.alert-danger {
    background: #fee2e2;
    color: #991b1b;
}
.alert-info {
    background: #1b4a9a;
    color: #ffffff;
}
.badge-success { background: #d1fae5; color: #065f46; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; }
.badge-danger { background: #fee2e2; color: #991b1b; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; }
.badge-warning { background: #fef3c7; color: #92400e; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; }
.text-danger { color: #dc2626; }

@media (max-width: 640px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }

    .config-table,
    .config-table tbody,
    .config-table tr,
    .config-table td {
        display: block;
        width: 100% !important;
    }

    .config-table tr {
        border-bottom: 1px solid #e2e8f0;
        padding: 0.25rem 0;
    }

    .config-table td {
        border-bottom: none;
        padding: 0.5rem 0.75rem;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
