<?php
/**
 * SDO CTS - Review Submission Page
 * Uses official form image as background with controlled text overlay
 */

session_start();

if (!isset($_SESSION['form_data']) || empty($_SESSION['form_data'])) {
    header('Location: index.php');
    exit;
}

$data = $_SESSION['form_data'];
$files = $_SESSION['form_files'] ?? [];

// Handle final submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_submit'])) {
    require_once __DIR__ . '/models/Complaint.php';
    
    try {
        $complaint = new Complaint();
        
        $complaintData = [
            'referred_to' => $data['referred_to'],
            'referred_to_other' => $data['referred_to_other'] ?? null,
            'name_pangalan' => $data['name_pangalan'],
            'address_tirahan' => $data['address_tirahan'],
            'contact_number' => $data['contact_number'],
            'email_address' => $data['email_address'],
            'involved_full_name' => $data['involved_full_name'],
            'involved_position' => $data['involved_position'],
            'involved_address' => $data['involved_address'],
            'involved_school_office_unit' => $data['involved_school_office_unit'],
            'narration_complaint' => $data['narration_complaint'],
            'narration_complaint_page2' => $data['narration_complaint_page2'] ?? null,
            'desired_action_relief' => $data['desired_action_relief'],
            'certification_agreed' => isset($data['certification_agreed']),
            'printed_name_pangalan' => $data['typed_signature'] ?? $data['name_pangalan'],
            'signature_type' => 'typed',
            'signature_data' => $data['typed_signature'] ?? $data['name_pangalan']
        ];
        
        $result = $complaint->create($complaintData);
        $complaintId = $result['id'];
        $referenceNumber = $result['reference_number'];
        
        if (!empty($files)) {
            $uploadDir = __DIR__ . '/uploads/complaints/' . $complaintId . '/';
            $tempDir = __DIR__ . '/uploads/temp/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            foreach ($files as $file) {
                $tempPath = $tempDir . $file['temp_name'];
                $newPath = $uploadDir . $file['temp_name'];
                if (file_exists($tempPath)) {
                    rename($tempPath, $newPath);
                    $complaint->addDocument($complaintId, $file['temp_name'], $file['original_name'], $file['type'], $file['size']);
                }
            }
        }
        
        unset($_SESSION['form_data']);
        unset($_SESSION['form_files']);
        
        $_SESSION['submission_success'] = [
            'reference_number' => $referenceNumber,
            'email' => $complaintData['email_address']
        ];
        
        header('Location: success.php');
        exit;
        
    } catch (Exception $e) {
        $error = "An error occurred. Please try again.";
    }
}

// Checkmarks for referred to section
$checkOSDS = $data['referred_to'] === 'OSDS' ? '✓' : '';
$checkSGOD = $data['referred_to'] === 'SGOD' ? '✓' : '';
$checkCID = $data['referred_to'] === 'CID' ? '✓' : '';
$checkOthers = $data['referred_to'] === 'Others' ? '✓' : '';
$othersText = ($data['referred_to'] === 'Others' && !empty($data['referred_to_other'])) ? $data['referred_to_other'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submission - SDO CTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Remove background pattern effect */
        body::before {
            display: none !important;
        }
        /* Form Container - Fixed size matching the official form */
        .form-container {
            position: relative;
            width: 850px;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Background Image Layer */
        .form-background {
            width: 100%;
            display: block;
        }
        
        /* Text Overlay Layer */
        .form-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        
        /* Base Field Container */
        .field-box {
            position: absolute;
            font-family: Arial, sans-serif;
            color: #000;
            overflow: hidden;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.3;
        }

        /* Checkmark Fields - precisely positioned in checkbox squares; made slightly bigger for clarity */
        .check-osds    { top: 5.65%; left: 52.8%; font-size: 14px; font-weight: bold; letter-spacing: 0.03em; }
        .check-sgod    { top: 5.65%; left: 62.05%; font-size: 14px; font-weight: bold; letter-spacing: 0.03em; }
        .check-cid     { top: 7.85%; left: 52.6%; font-size: 14px; font-weight: bold; letter-spacing: 0.03em; }
        .check-others  { top: 7.65%; left: 62.10%; font-size: 14px; font-weight: bold; letter-spacing: 0.03em; }

        /* Others Text Field */
        .others-text-box {
            top: 7.65%;
            left: 80%;
            width: 29%;
            height: 2%;
            font-size: 11px;
        }
        
        /* Date Field - on the Date/Petsa line */
        .date-box {
            top: 12.7%;
            left: 67%;
            width: 38%;
            height: 1.8%;
            font-size: 12px;
        }
        
        /* Complainant Name */
        .complainant-name-box {
            top: 31.5%;
            left: 24%;
            width: 66%;
            height: 1.2%;
            font-size: 10px;
        }
        
        /* Complainant Address */
        .complainant-address-box {
            top: 33.2%;
            left: 24%;
            width: 66%;
            height: 1.2%;
            font-size: 10px;
        }
        
        /* Complainant Contact */
        .complainant-contact-box {
            top: 34.9%;
            left: 24%;
            width: 66%;
            height: 1.2%;
            font-size: 10px;
        }
        
        /* Complainant Email */
        .complainant-email-box {
            top: 36.6%;
            left: 24%;
            width: 66%;
            height: 1.2%;
            font-size: 10px;
        }
        
        /* Involved Person Name */
        .involved-name-box {
            top: 42.1%;
            left: 24%;
            width: 73%;
            height: 1.2%;
            font-size: 10px;
        }
        
        /* Involved Position */
        .involved-position-box {
            top: 44.0%;
            left: 24%;
            width: 73%;
            height: 1.2%;
            font-size: 10px;
        }
        
        /* Involved Address */
        .involved-address-box {
            top: 45.7%;
            left: 24%;
            width: 73%;
            height: 1.2%;
            font-size: 10px;
        }
        
        /* Involved School/Office */
        .involved-school-box {
            top: 47.4%;
            left: 24%;
            width: 69%;
            height: 1.2%;
            font-size: 10px;
        }
        
        /* Narration Box - Multi-line with controlled height */
        .narration-box {
            top: 55.5%;
            left: 10%;
            width: 80%;
            height: 15%;
            font-size: 9.5px;
            line-height: 2.05;
            overflow: hidden;
            white-space: pre-wrap;
            word-break: break-word;
        }
        
        /* Signature Box */
        .signature-box {
            top: 93%;
            left: 28%;
            width: 44%;
            height: 2%;
            font-family: 'Times New Roman', Times, serif;
            font-size: 16px;
            text-align: center;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Review Banner */
        .review-banner {
            background: transparent;
            color: var(--text-primary);
            padding: 0 0 10px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .review-banner .icon { font-size: 1.5rem; color: var(--primary-color); }
        .review-banner h3 { margin: 0 0 4px; font-size: 1.05rem; }
        .review-banner p { margin: 0; font-size: 0.85rem; color: var(--text-secondary); }
        
        .attached-notice {
            background: #f5f5f5;
            padding: 12px 15px;
            margin-top: 15px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        
        /* Additional Page Styles - Official Form Look */
        .additional-page {
            position: relative;
            width: 850px;
            max-width: 100%;
            margin: 30px auto 0;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px 40px;
            min-height: 1100px;
        }
        
        .additional-page-header {
            text-align: center;
            margin-bottom: 0;
            border: 1px solid #000;
            border-left: 3px solid #000;
            border-right: 3px solid #000;
            border-bottom: none;
            padding: 10px;
            background: #fff;
        }
        
        .additional-page-header h2 {
            color: #000;
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 5px;
            text-transform: uppercase;
            letter-spacing: 0;
        }
        
        .additional-page-header p {
            color: #000;
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            font-style: italic;
            margin: 0;
        }
        
        .additional-page-content {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            line-height: 28px;
            white-space: pre-wrap;
            word-break: break-word;
            min-height: 900px;
            padding: 5px 10px;
            background: repeating-linear-gradient(
                transparent,
                transparent 27px,
                #000 27px,
                #000 28px
            );
            border: 1px solid #000;
            border-left: 3px solid #000;
            border-right: 3px solid #000;
            border-bottom: 3px solid #000;
        }
        
        .page-indicator {
            text-align: center;
            color: #999;
            font-size: 0.85rem;
            margin-top: 15px;
        }
        
        .page-number-label {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            text-align: right;
            margin-bottom: 10px;
            color: #000;
        }
        
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; background: #fff; }
            .container { max-width: 100%; padding: 0; }
            .form-container { box-shadow: none; page-break-after: always; }
            .additional-page { box-shadow: none; margin-top: 0; page-break-before: always; }
        }
        
        @media (max-width: 850px) {
            .form-container {
                width: 100%;
            }
            .field-box {
                font-size: 9px;
            }
            .narration-box {
                font-size: 8px;
            }
            .signature-box {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="nav-bar no-print">
            <div class="nav-links">
                <a href="index.php" class="active"><i class="fas fa-file-alt"></i> File Complaint</a>
                <a href="track.php"><i class="fas fa-search"></i> Track Complaint</a>
                <a href="contact.php"><i class="fas fa-phone-alt"></i> Contact Us</a>
            </div>
        </nav>

        <?php if (isset($error)): ?>
        <div class="no-print" style="background:#f8d7da;color:#721c24;padding:15px;border-radius:8px;margin-bottom:20px;">
            ⚠️ <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="review-banner no-print" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
            <span class="icon" style="font-size: 1.5rem; margin-bottom: 1px;"><i class="fas fa-clipboard-list"></i></span>
            <div>
                <h3 style="margin: 5 0 7px;">Review Your Complaint Assisted Form</h3>
                <p style="margin: 0;">Verify all the information below are correct.</p>
            </div>
        </div>

        <!-- PAGE 1: FORM WITH IMAGE BACKGROUND AND TEXT OVERLAY -->
        <div class="form-container">
            <!-- Background Image (Official Form) -->
            <img src="reference/COMPLAINT-ASSISTED-FORM_1.jpg" 
                 alt="Complaint Assisted Form" 
                 class="form-background">
            
            <!-- Text Overlay Layer with Positioned Field Boxes -->
            <div class="form-overlay">
                
                <!-- Routing Checkmarks -->
                <div class="field-box check-osds"><?php echo $checkOSDS; ?></div>
                <div class="field-box check-sgod"><?php echo $checkSGOD; ?></div>
                <div class="field-box check-cid"><?php echo $checkCID; ?></div>
                <div class="field-box check-others"><?php echo $checkOthers; ?></div>
                
                <!-- Others Text -->
                <div class="field-box others-text-box"><?php echo htmlspecialchars($othersText); ?></div>
                
                <!-- Date -->
                <div class="field-box date-box"><?php echo date('F j, Y'); ?></div>
                
                <!-- Complainant Information -->
                <div class="field-box complainant-name-box"><?php echo htmlspecialchars($data['name_pangalan']); ?></div>
                <div class="field-box complainant-address-box"><?php echo htmlspecialchars($data['address_tirahan']); ?></div>
                <div class="field-box complainant-contact-box"><?php echo htmlspecialchars($data['contact_number']); ?></div>
                <div class="field-box complainant-email-box"><?php echo htmlspecialchars($data['email_address']); ?></div>
                
                <!-- Involved Person/Office -->
                <div class="field-box involved-name-box"><?php echo htmlspecialchars($data['involved_full_name']); ?></div>
                <div class="field-box involved-position-box"><?php echo htmlspecialchars($data['involved_position']); ?></div>
                <div class="field-box involved-address-box"><?php echo htmlspecialchars($data['involved_address']); ?></div>
                <div class="field-box involved-school-box"><?php echo htmlspecialchars($data['involved_school_office_unit']); ?></div>
                
                <!-- Narration (Multi-line, Controlled) -->
                <div class="field-box narration-box"><?php echo htmlspecialchars($data['narration_complaint']); ?></div>
                
                <!-- Signature -->
                <div class="field-box signature-box"><?php echo htmlspecialchars($data['typed_signature'] ?? $data['name_pangalan']); ?></div>
                
            </div>
        </div>
        <div class="page-indicator no-print">Page 1 of <?php echo !empty($data['narration_complaint_page2']) ? '2' : '1'; ?></div>

        <!-- PAGE 2: ADDITIONAL PAGE FOR NARRATION CONTINUATION (Only if content exists) -->
        <?php if (!empty($data['narration_complaint_page2'])): ?>
        <div class="additional-page">
            <div class="page-number-label"></div>
            
            <div class="additional-page-header">
                <h2>CONTINUATION OF NARRATION OF COMPLAINT / INQUIRY AND RELIEF</h2>
                <p>(Ano ang iyong reklamo, tanong, request o suhestiyon? Ano ang gusto mong aksiyon?)</p>
            </div>
            
            <div class="additional-page-content"><?php echo htmlspecialchars($data['narration_complaint_page2']); ?></div>
        </div>
        <div class="page-indicator no-print">Page 2 of 2</div>
        <?php endif; ?>

        <!-- Attached Files (Below Form) -->
        <?php if (!empty($files)): ?>
        <div class="attached-notice no-print">
            <strong>📎 Attached Supporting Documents:</strong>
            <ul style="margin:8px 0 0 20px;padding:0;">
                <?php foreach ($files as $file): ?>
                <li><?php echo htmlspecialchars($file['original_name']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <form method="POST" class="form-actions no-print" style="margin-top:20px;">
            <a href="index.php?edit=1" class="btn btn-secondary">⬅️ Go Back & Edit</a>
            <div style="display:flex;gap:10px;">
                <button type="button" class="btn btn-outline" onclick="window.print()">🖨️ Print</button>
                <button type="submit" name="confirm_submit" class="btn btn-success btn-lg">✅ Confirm & Submit</button>
            </div>
        </form>

        <footer class="form-footer no-print">
            <p>SDO CTS - San Pedro Division Office Complaint Tracking System</p>
        </footer>
    </div>
</body>
</html>
