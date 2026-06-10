<?php
/**
 * Admin Panel Installation Script
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 * 
 * Run this script once to set up the admin tables and default admin user.
 * DELETE THIS FILE AFTER INSTALLATION FOR SECURITY!
 */

require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$messages = [];
$errors = [];

// Check if already installed
try {
    $result = $db->query("SELECT COUNT(*) as count FROM admin_users")->fetch();
    if ($result['count'] > 0) {
        $messages[] = "Admin panel appears to be already installed. Found {$result['count']} admin user(s).";
        $alreadyInstalled = true;
    } else {
        $alreadyInstalled = false;
    }
} catch (Exception $e) {
    $alreadyInstalled = false;
}

// Handle installation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    try {
        // Create admin_roles table
        $db->query("
            CREATE TABLE IF NOT EXISTS admin_roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) UNIQUE NOT NULL,
                description VARCHAR(255),
                permissions JSON,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");
        $messages[] = "[OK] Created admin_roles table";

        // Create admin_users table
        $db->query("
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) DEFAULT NULL,
                full_name VARCHAR(255) NOT NULL,
                role_id INT NOT NULL,
                google_id VARCHAR(255) DEFAULT NULL,
                avatar_url VARCHAR(500) DEFAULT NULL,
                unit VARCHAR(50) DEFAULT NULL,
                is_active TINYINT(1) DEFAULT 1,
                last_login TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_by INT DEFAULT NULL,
                INDEX idx_email (email),
                INDEX idx_google_id (google_id)
            ) ENGINE=InnoDB
        ");
        $messages[] = "[OK] Created admin_users table";

        // Create activity_log table
        $db->query("
            CREATE TABLE IF NOT EXISTS activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT DEFAULT NULL,
                action_type ENUM('login', 'logout', 'view', 'create', 'update', 'delete', 'status_change', 'forward', 'accept', 'return') NOT NULL,
                entity_type VARCHAR(50) NOT NULL,
                entity_id INT DEFAULT NULL,
                description TEXT,
                old_value JSON DEFAULT NULL,
                new_value JSON DEFAULT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_action_type (action_type),
                INDEX idx_entity (entity_type, entity_id),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB
        ");
        $messages[] = "[OK] Created activity_log table";

        // Create complaint_assignments table
        $db->query("
            CREATE TABLE IF NOT EXISTS complaint_assignments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                complaint_id INT NOT NULL,
                assigned_to_unit VARCHAR(50) NOT NULL,
                assigned_by INT NOT NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_complaint_id (complaint_id)
            ) ENGINE=InnoDB
        ");
        $messages[] = "[OK] Created complaint_assignments table";

        // Add new columns to complaints table (ignore if already exists)
        $alterColumns = [
            "ADD COLUMN accepted_at TIMESTAMP NULL",
            "ADD COLUMN accepted_by INT NULL",
            "ADD COLUMN returned_at TIMESTAMP NULL",
            "ADD COLUMN returned_by INT NULL",
            "ADD COLUMN return_reason TEXT NULL",
            "ADD COLUMN assigned_unit VARCHAR(50) NULL",
            "ADD COLUMN handled_by INT NULL"
        ];

        foreach ($alterColumns as $alter) {
            try {
                $db->query("ALTER TABLE complaints $alter");
            } catch (Exception $e) {
                // Column might already exist
            }
        }
        $messages[] = "[OK] Updated complaints table with new columns";

        // Update status enum
        try {
            $db->query("ALTER TABLE complaints MODIFY COLUMN status ENUM('pending', 'accepted', 'in_progress', 'resolved', 'returned', 'closed') NOT NULL DEFAULT 'pending'");
            $messages[] = "[OK] Updated complaints status enum";
        } catch (Exception $e) {
            // Might already be updated
        }

        // Add admin_user_id to complaint_history
        try {
            $db->query("ALTER TABLE complaint_history ADD COLUMN admin_user_id INT NULL");
        } catch (Exception $e) {
            // Might already exist
        }

        // Update complaint_history status enum
        try {
            $db->query("ALTER TABLE complaint_history MODIFY COLUMN status ENUM('pending', 'accepted', 'in_progress', 'resolved', 'returned', 'closed') NOT NULL");
        } catch (Exception $e) {
            // Might already be updated
        }
        $messages[] = "[OK] Updated complaint_history table";

        // Insert default roles
        $roles = [
            ['Super Admin', 'Full system access', '{"all": true}'],
            ['Admin', 'Manage complaints and users', '{"complaints": true, "users": true, "reports": true, "logs": true}'],
            ['Staff', 'Process complaints', '{"complaints": true, "complaints.view": true, "complaints.update": true}'],
            ['Viewer', 'View only access', '{"complaints.view": true}']
        ];

        foreach ($roles as $role) {
            try {
                $db->query("INSERT INTO admin_roles (name, description, permissions) VALUES (?, ?, ?)", $role);
            } catch (Exception $e) {
                // Role might already exist
            }
        }
        $messages[] = "[OK] Inserted default roles";

        // Create default admin user
        $adminEmail = trim($_POST['admin_email'] ?? 'admin@deped-sanpedro.ph');
        $adminPassword = $_POST['admin_password'] ?? 'admin123';
        $adminName = trim($_POST['admin_name'] ?? 'System Administrator');

        if (!empty($adminEmail) && !empty($adminPassword)) {
            $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
            
            try {
                $db->query(
                    "INSERT INTO admin_users (email, password_hash, full_name, role_id, unit, is_active) VALUES (?, ?, ?, 1, 'OSDS', 1)",
                    [$adminEmail, $passwordHash, $adminName]
                );
                $messages[] = "[OK] Created admin user: $adminEmail";
            } catch (Exception $e) {
                $errors[] = "Admin user might already exist: " . $e->getMessage();
            }
        }

        $messages[] = "";
        $messages[] = "[SUCCESS] Installation completed successfully!";
        $messages[] = "";
        $messages[] = "[WARNING] IMPORTANT: Delete this file (install.php) for security!";
        $messages[] = "";
        $messages[] = "You can now login at: /CTS/admin/login.php";
        
    } catch (Exception $e) {
        $errors[] = "Installation error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel Installation - SDO CTS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #1b4a9a 0%, #1b4a9a 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: #e8f1f8;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .card {
            background: rgba(17, 29, 46, 0.95);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.18);
            border: 1px solid rgba(187, 225, 250, 0.1);
        }
        
        h1 {
            text-align: center;
            margin-bottom: 8px;
            font-size: 1.75rem;
        }
        
        .subtitle {
            text-align: center;
            color: #7a9bb8;
            margin-bottom: 32px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #e8f1f8;
        }
        
        input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(187, 225, 250, 0.2);
            border-radius: 8px;
            color: #e8f1f8;
            font-size: 1rem;
            font-family: inherit;
        }
        
        input:focus {
            outline: none;
            border-color: #1b4a9a;
            box-shadow: 0 0 0 3px rgba(27, 74, 154, 0.2);
        }
        
        .btn {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #1b4a9a 0%, #1b4a9a 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(27, 74, 154, 0.4);
        }
        
        .messages {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
        }
        
        .messages p {
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .messages p:last-child {
            margin-bottom: 0;
        }
        
        .errors {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
            color: #fca5a5;
        }
        
        .warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            color: #fcd34d;
            font-size: 0.9rem;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: #7a9bb8;
            text-decoration: none;
        }
        
        .back-link:hover {
            color: #e8f1f8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><i class="fas fa-cog"></i> SDO CTS Admin Installation</h1>
            <p class="subtitle">Set up the admin panel for the Complaint Tracking System</p>
            
            <?php if (!empty($messages)): ?>
            <div class="messages">
                <?php foreach ($messages as $msg): ?>
                <p><?php echo htmlspecialchars($msg); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $err): ?>
                <p>❌ <?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($alreadyInstalled): ?>
            <div class="warning">
                <i class="fas fa-exclamation-triangle"></i> The admin panel is already installed. Running this again will attempt to add a new admin user.
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="admin_name">Admin Full Name</label>
                    <input type="text" id="admin_name" name="admin_name" value="System Administrator" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_email">Admin Email</label>
                    <input type="email" id="admin_email" name="admin_email" value="admin@deped-sanpedro.ph" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">Admin Password</label>
                    <input type="password" id="admin_password" name="admin_password" value="admin123" required>
                    <small style="color: #7a9bb8; display: block; margin-top: 4px;">Change this immediately after installation!</small>
                </div>
                
                <button type="submit" name="install" class="btn">🚀 Install Admin Panel</button>
            </form>
            
            <a href="/CTS/" class="back-link">← Back to Public Site</a>
        </div>
    </div>
</body>
</html>

