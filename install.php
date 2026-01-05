<?php
/**
 * SDO CTS - Database Installation Script
 * Run this script once to create the database and tables
 * Based on Official DepEd Complaint Assisted Form
 */

$host = 'localhost';
$user = 'root';
$pass = '';

echo "<html><head>";
echo "<title>SDO CTS - Installation</title>";
echo "<link rel='preconnect' href='https://fonts.googleapis.com'>";
echo "<link href='https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap' rel='stylesheet'>";
echo "<style>";
echo "body { font-family: 'DM Sans', sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f4f7f6; }";
echo ".card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }";
echo "h1 { color: #1a5f7a; margin-bottom: 20px; }";
echo ".success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 10px 0; }";
echo ".error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 10px 0; }";
echo ".info { background: #cce5ff; color: #004085; padding: 15px; border-radius: 8px; margin: 10px 0; }";
echo ".btn { display: inline-block; background: #1a5f7a; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; margin-top: 20px; }";
echo ".btn:hover { background: #0d3d4d; }";
echo "</style>";
echo "</head><body>";
echo "<div class='card'>";
echo "<h1>🏛️ SDO CTS Installation</h1>";
echo "<p style='color: #666; margin-bottom: 20px;'>San Pedro Division Office Complaint Tracking System<br><small>Based on Official DepEd Complaint Assisted Form</small></p>";

$success = true;
$messages = [];

try {
    // Connect without database
    $pdo = new PDO("mysql:host={$host}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $messages[] = ['type' => 'success', 'text' => '✅ Connected to MySQL server'];

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS sdo_cts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $messages[] = ['type' => 'success', 'text' => '✅ Database "sdo_cts" created or already exists'];

    // Select database
    $pdo->exec("USE sdo_cts");

    // Drop existing tables if needed (for fresh install)
    // Uncomment if you want to reset the database
    // $pdo->exec("DROP TABLE IF EXISTS complaint_history, complaint_documents, complaints");

    // Create complaints table with Official Form field names
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS complaints (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reference_number VARCHAR(20) UNIQUE NOT NULL,
            
            -- Referred to (indicate unit/section)
            referred_to ENUM('OSDS', 'SGOD', 'CID', 'Others') NOT NULL,
            referred_to_other VARCHAR(255) DEFAULT NULL,
            date_petsa DATETIME NOT NULL,
            
            -- Complainant/Requestor Information
            name_pangalan VARCHAR(255) NOT NULL,
            address_tirahan TEXT NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            email_address VARCHAR(255) NOT NULL,
            
            -- Office/School/Person Involved
            involved_full_name VARCHAR(255) NOT NULL,
            involved_position VARCHAR(255) NOT NULL,
            involved_address TEXT NOT NULL,
            involved_school_office_unit VARCHAR(255) NOT NULL,
            
            -- Narration of Complaint/Inquiry and Relief
            narration_complaint TEXT NOT NULL,
            desired_action_relief TEXT NOT NULL,
            
            -- Certification on Non-Forum Shopping
            certification_agreed TINYINT(1) NOT NULL DEFAULT 0,
            
            -- Name and Signature / Pangalan at Lagda
            printed_name_pangalan VARCHAR(255) NOT NULL,
            signature_type ENUM('digital', 'typed') NOT NULL DEFAULT 'typed',
            signature_data TEXT DEFAULT NULL,
            date_signed DATE NOT NULL,
            
            -- Status Tracking
            status ENUM('pending', 'under_review', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'pending',
            is_locked TINYINT(1) NOT NULL DEFAULT 1,
            
            -- Timestamps
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_reference (reference_number),
            INDEX idx_status (status),
            INDEX idx_date_petsa (date_petsa)
        ) ENGINE=InnoDB
    ");
    $messages[] = ['type' => 'success', 'text' => '✅ Table "complaints" created (with Official Form fields)'];

    // Create documents table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS complaint_documents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            complaint_id INT NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_type VARCHAR(50) NOT NULL,
            file_size INT NOT NULL,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
            INDEX idx_complaint_id (complaint_id)
        ) ENGINE=InnoDB
    ");
    $messages[] = ['type' => 'success', 'text' => '✅ Table "complaint_documents" created'];

    // Create history table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS complaint_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            complaint_id INT NOT NULL,
            status ENUM('pending', 'under_review', 'in_progress', 'resolved', 'closed') NOT NULL,
            notes TEXT DEFAULT NULL,
            updated_by VARCHAR(255) DEFAULT 'System',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
            INDEX idx_complaint_id (complaint_id)
        ) ENGINE=InnoDB
    ");
    $messages[] = ['type' => 'success', 'text' => '✅ Table "complaint_history" created'];

    // Create upload directories
    $uploadDirs = [
        __DIR__ . '/uploads',
        __DIR__ . '/uploads/temp',
        __DIR__ . '/uploads/complaints'
    ];

    foreach ($uploadDirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    $messages[] = ['type' => 'success', 'text' => '✅ Upload directories created'];

    // Create .htaccess for uploads security
    $htaccess = __DIR__ . '/uploads/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Options -Indexes\n<FilesMatch '\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|html|htm|shtml|sh|cgi)$'>\n    Order Deny,Allow\n    Deny from all\n</FilesMatch>");
    }
    $messages[] = ['type' => 'success', 'text' => '✅ Security .htaccess created for uploads'];

} catch (PDOException $e) {
    $success = false;
    $messages[] = ['type' => 'error', 'text' => '❌ Error: ' . $e->getMessage()];
}

// Display messages
foreach ($messages as $msg) {
    echo "<div class='{$msg['type']}'>{$msg['text']}</div>";
}

if ($success) {
    echo "<div class='info'>";
    echo "<strong>📌 Installation Complete!</strong><br><br>";
    echo "Your SDO CTS system is now ready to use.<br>";
    echo "Database tables are aligned with the Official DepEd Complaint Assisted Form.<br><br>";
    echo "<strong>Form Fields Created:</strong><br>";
    echo "• Referred to (indicate unit/section): OSDS, SGOD, CID, Others<br>";
    echo "• Date/Petsa<br>";
    echo "• Complainant/Requestor Information: Name/Pangalan, Address/Tirahan, Contact Number, E-mail address<br>";
    echo "• Office/School/Person Involved: Full Name, Position, Address, School/Office/Unit<br>";
    echo "• Narration of Complaint/Inquiry and Relief<br>";
    echo "• Certification on Non-Forum Shopping<br>";
    echo "• Name and Signature / Pangalan at Lagda<br>";
    echo "</div>";
    echo "<a href='index.php' class='btn'>🚀 Launch SDO CTS</a>";
} else {
    echo "<div class='error'>";
    echo "<strong>Installation Failed</strong><br>";
    echo "Please check the error messages above and ensure MySQL is running.";
    echo "</div>";
}

echo "</div></body></html>";
?>
