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
$isHandwritten = !empty($data['handwritten_mode']);

// Handle final submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_submit'])) {
    require_once __DIR__ . '/models/Complaint.php';
    require_once __DIR__ . '/services/email/ComplaintNotification.php';
    
    // Get complaint information from form data
    $complainantName = $data['name_pangalan'] ?? '';
    $complainantEmail = $data['email_address'] ?? '';
    $complainantContact = $data['contact_number'] ?? '';
    
    // For bypass mode, get values from review page inputs
    if ($isHandwritten) {
        $complainantName = trim($_POST['bypass_name'] ?? $complainantName);
        $complainantEmail = trim($_POST['bypass_email'] ?? $complainantEmail);
        $complainantContact = trim($_POST['bypass_contact'] ?? $complainantContact);
        // Also get the unit selection
        if (!empty($_POST['bypass_unit'])) {
            $data['involved_school_office_unit'] = trim($_POST['bypass_unit']);
        }
    }
    
    // Validate all required fields
    if (empty($complainantName) || empty($complainantEmail) || empty($complainantContact)) {
        $error = "Error: Complainant information is incomplete. Please fill in Name, Email, and Contact Number.";
    } else {
        // Update form data with validated information
        $data['name_pangalan'] = $complainantName;
        $data['email_address'] = $complainantEmail;
        $data['contact_number'] = $complainantContact;
        
        try {
            $complaint = new Complaint();
            
            $complaintData = [
                'referred_to' => $data['referred_to'] ?? 'OSDS',
                'referred_to_other' => $data['referred_to_other'] ?? null,
                'name_pangalan' => $complainantName,
                'address_tirahan' => $data['address_tirahan'] ?? null,
                'contact_number' => $complainantContact,
                'email_address' => $complainantEmail,
                'involved_full_name' => $data['involved_full_name'] ?? null,
                'involved_position' => $data['involved_position'] ?? null,
                'involved_address' => $data['involved_address'] ?? null,
                'involved_school_office_unit' => $data['involved_school_office_unit'] ?? null,
                'narration_complaint' => $data['narration_complaint'] ?? null,
                'narration_complaint_page2' => $data['narration_complaint_page2'] ?? null,
                'desired_action_relief' => $data['desired_action_relief'] ?? null,
                'certification_agreed' => !empty($data['certification_agreed']),
                'printed_name_pangalan' => $data['typed_signature'] ?? $complainantName,
                'signature_type' => $isHandwritten ? 'uploaded_form' : 'typed',
                'signature_data' => $isHandwritten ? null : ($data['typed_signature'] ?? $complainantName)
            ];
            
            $result = $complaint->create($complaintData);
            $complaintId = $result['id'];
            $referenceNumber = $result['reference_number'];
            
            if (!empty($files)) {
                $tempDir = __DIR__ . '/uploads/temp/';
                $imagesDir = __DIR__ . '/assets/uploads/images/';
                $documentsDir = __DIR__ . '/assets/uploads/documents/';
                
                // Ensure directories exist
                if (!is_dir($imagesDir)) mkdir($imagesDir, 0755, true);
                if (!is_dir($documentsDir)) mkdir($documentsDir, 0755, true);
                
                foreach ($files as $file) {
                    $tempPath = $tempDir . $file['temp_name'];
                    
                    if (file_exists($tempPath)) {
                        $category = $file['category'] ?? 'supporting';
                        $ext = strtolower(pathinfo($file['temp_name'], PATHINFO_EXTENSION));
                        
                        // Determine file type and target directory
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png']);
                        $targetDir = $isImage ? $imagesDir : $documentsDir;
                        $targetRelativeDir = $isImage ? 'assets/uploads/images/' : 'assets/uploads/documents/';
                        
                        // Create filename: complaint_[id]_[category]_[timestamp].[ext]
                        $timestamp = time() . '_' . uniqid();
                        $newFileName = "complaint_{$complaintId}_{$category}_{$timestamp}.{$ext}";
                        $newPath = $targetDir . $newFileName;
                        $relativePath = $targetRelativeDir . $newFileName;
                        
                        // Move file to centralized folder
                        if (rename($tempPath, $newPath)) {
                            // Store with relative path
                            $complaint->addDocument($complaintId, $newFileName, $file['original_name'], $file['type'], $file['size'], $category, $relativePath);
                        }
                    }
                }
            }
            
            // Send email notifications (does not interrupt if fails)
            try {
                $notificationData = array_merge($complaintData, [
                    'id' => $complaintId,
                    'reference_number' => $referenceNumber
                ]);
                $emailNotification = new ComplaintNotification();
                $emailNotification->sendComplaintSubmittedNotification($notificationData);
            } catch (Exception $emailError) {
                // Log email error but don't interrupt the submission process
                error_log("Email notification error: " . $emailError->getMessage());
            }
            
            unset($_SESSION['form_data']);
            unset($_SESSION['form_files']);
            
            $_SESSION['submission_success'] = [
                'reference_number' => $referenceNumber,
                'email' => $complainantEmail
            ];
            
            header('Location: success.php');
            exit;
            
        } catch (Exception $e) {
            $error = "An error occurred. Please try again.";
        }
    }
}

// Checkmarks for referred to section
// Public form no longer asks the complainant to select the routing unit,
// so leave all checkboxes blank on the review/printable form.
$checkOSDS = '';
$checkSGOD = '';
$checkCID = '';
$checkOthers = '';
$othersText = ($data['referred_to'] === 'Others' && !empty($data['referred_to_other'])) ? $data['referred_to_other'] : '';

function isPrintableImage($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
}

function isPrintablePdf($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return $ext === 'pdf';
}
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
            box-shadow: 0 1px 6px rgba(0,0,0,0.08);
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
            box-shadow: 0 1px 6px rgba(0,0,0,0.08);
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

        .doc-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .doc-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 8px;
            gap: 12px;
        }
        .doc-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }
        .doc-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .bypass-contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 0;
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

        @media (max-width: 768px) {
            .review-banner {
                padding-bottom: 0.75rem;
            }

            .doc-item {
                flex-direction: column;
                align-items: stretch;
            }

            .doc-info {
                width: 100%;
            }

            .doc-actions {
                width: 100%;
                flex-wrap: wrap;
                gap: 10px;
            }

            .doc-actions .btn {
                flex: 1 1 calc(50% - 10px);
                justify-content: center;
            }

            .bypass-contact-grid {
                grid-template-columns: 1fr;
            }

            .complainant-info-table,
            .complainant-info-table tbody,
            .complainant-info-table tr,
            .complainant-info-table td {
                display: block;
                width: 100% !important;
            }

            .complainant-info-table tr {
                border-bottom: 1px solid #1b4a9a;
            }

            .complainant-info-table tr:last-child {
                border-bottom: none;
            }

            .complainant-info-table td {
                padding: 10px 12px !important;
            }

            .form-actions {
                gap: 0.75rem;
            }

            .form-actions .review-action-group {
                width: 100%;
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .form-actions .review-action-group .btn {
                flex: 1 1 100%;
                width: 100%;
                justify-content: center;
            }

            .additional-page {
                padding: 20px 14px;
                min-height: auto;
            }

            .additional-page-content {
                min-height: 560px;
                line-height: 24px;
            }
        }

        @media (max-width: 480px) {
            .doc-actions .btn {
                flex: 1 1 100%;
            }

            .form-container,
            .additional-page {
                box-shadow: none;
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

        <?php if ($isHandwritten): ?>
        <div class="no-print" style="background:#1b4a9a;color:#ffffff;padding:15px;border-radius:8px;margin-bottom:20px;border:1px solid #1b4a9a;">
            <strong>Handwritten Form Attached:</strong>
            This submission includes an uploaded photo or scan of a fully accomplished Complaints-Assisted Form.
            On-page fields may appear blank because the official details are contained in the attached form.
        </div>
        <?php endif; ?>

        <div class="review-banner no-print" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
            <span class="icon" style="font-size: 1.5rem; margin-bottom: 1px;"><i class="fas fa-clipboard-list"></i></span>
            <div>
                <h3 style="margin: 5 0 7px;">
                    <?php echo $isHandwritten ? 'Review Your Uploaded Complaint-Assisted Form' : 'Review Your Complaint Assisted Form'; ?>
                </h3>
                <p style="margin: 0;">
                    <?php echo $isHandwritten
                        ? 'Please review the uploaded file(s) below before submitting.'
                        : 'Verify all the information below are correct.'; ?>
                </p>
            </div>
        </div>
        
        <?php if ($isHandwritten): ?>
            <!-- Handwritten mode: show uploaded file(s) instead of blank official form -->
            <?php
            $tempDirUrl = 'uploads/temp/';
            $handwrittenFiles = array_filter($files, function($f) {
                return isset($f['category']) && $f['category'] === 'handwritten_form';
            });
            $validIdFiles = array_filter($files, function($f) {
                return isset($f['category']) && $f['category'] === 'valid_id';
            });
            $supportingFiles = array_filter($files, function($f) {
                $cat = $f['category'] ?? 'supporting';
                return $cat === 'supporting';
            });
            ?>
            <?php if (!empty($handwrittenFiles)): ?>
            <section class="form-section no-print" style="margin-top:10px;">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-file-signature"></i></span>
                    Uploaded Completed Complaint-Assisted Form
                </div>
                <div class="section-content">
                    <ul class="doc-list">
                        <?php foreach ($handwrittenFiles as $file): ?>
                        <?php
                            $fileName = $file['original_name'] ?? 'Attachment';
                            $url = $tempDirUrl . rawurlencode($file['temp_name']);
                            $isImage = isPrintableImage($fileName);
                            $isPdf = isPrintablePdf($fileName);
                            $docType = $isImage ? 'image' : ($isPdf ? 'pdf' : 'other');
                            $iconClass = $isImage ? 'fa-image' : ($isPdf ? 'fa-file-pdf' : 'fa-file');
                            $iconColor = $isImage ? '#10b981' : ($isPdf ? '#ef4444' : '#6b7280');
                        ?>
                        <li class="doc-item">
                            <div class="doc-info">
                                <i class="fas <?php echo $iconClass; ?>" style="font-size:24px;color:<?php echo $iconColor; ?>;flex-shrink:0;"></i>
                                <div style="min-width:0;">
                                    <div style="font-weight:500;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:300px;">
                                        <?php echo htmlspecialchars($fileName); ?>
                                    </div>
                                    <div style="font-size:12px;color:#6b7280;">
                                        <?php echo number_format(($file['size'] ?? 0) / 1024, 1); ?> KB
                                    </div>
                                </div>
                            </div>
                            <div class="doc-actions">
                                <button type="button" class="btn btn-sm btn-outline doc-modal-btn" data-url="<?php echo htmlspecialchars($url); ?>" data-type="<?php echo $docType; ?>" data-name="<?php echo htmlspecialchars($fileName); ?>" title="View in popup">
                                    <i class="fas fa-expand"></i> View
                                </button>
                                <button type="button" class="btn btn-sm btn-outline doc-print-btn" data-url="<?php echo htmlspecialchars($url); ?>" data-type="<?php echo $docType; ?>" data-name="<?php echo htmlspecialchars($fileName); ?>" data-category="<?php echo htmlspecialchars($file['category'] ?? 'supporting'); ?>" title="Print">
                                    <i class="fas fa-print"></i>
                                </button>
                                <a href="<?php echo htmlspecialchars($url); ?>" download="<?php echo htmlspecialchars($fileName); ?>" class="btn btn-sm btn-primary" title="Download" style="color:#fff !important;">
                                    <i class="fas fa-download" style="color:#fff;"></i> <span class="d-none d-sm-inline">Download</span>
                                </a>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
            <?php endif; ?>

            <?php if (!empty($validIdFiles)): ?>
            <section class="form-section no-print">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-paperclip"></i></span>
                    Additional Attached Documents
                </div>
                <div class="section-content">
                    <ul class="doc-list">
                        <?php foreach ($validIdFiles as $file): ?>
                        <?php
                            $fileName = $file['original_name'] ?? 'Attachment';
                            $url = $tempDirUrl . rawurlencode($file['temp_name']);
                            $isImage = isPrintableImage($fileName);
                            $isPdf = isPrintablePdf($fileName);
                            $docType = $isImage ? 'image' : ($isPdf ? 'pdf' : 'other');
                            $iconClass = $isImage ? 'fa-image' : ($isPdf ? 'fa-file-pdf' : 'fa-file');
                            $iconColor = $isImage ? '#10b981' : ($isPdf ? '#ef4444' : '#6b7280');
                        ?>
                        <li class="doc-item">
                            <div class="doc-info">
                                <i class="fas <?php echo $iconClass; ?>" style="font-size:24px;color:<?php echo $iconColor; ?>;flex-shrink:0;"></i>
                                <div style="min-width:0;">
                                    <div style="font-weight:500;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:300px;">
                                        <?php echo htmlspecialchars($fileName); ?>
                                    </div>
                                    <div style="font-size:12px;color:#6b7280;">
                                        <?php echo number_format(($file['size'] ?? 0) / 1024, 1); ?> KB
                                    </div>
                                </div>
                            </div>
                            <div class="doc-actions">
                                <button type="button" class="btn btn-sm btn-outline doc-modal-btn" data-url="<?php echo htmlspecialchars($url); ?>" data-type="<?php echo $docType; ?>" data-name="<?php echo htmlspecialchars($fileName); ?>" title="View in popup">
                                    <i class="fas fa-expand"></i> View
                                </button>
                                <button type="button" class="btn btn-sm btn-outline doc-print-btn" data-url="<?php echo htmlspecialchars($url); ?>" data-type="<?php echo $docType; ?>" data-name="<?php echo htmlspecialchars($fileName); ?>" data-category="<?php echo htmlspecialchars($file['category'] ?? 'valid_id'); ?>" title="Print">
                                    <i class="fas fa-print"></i>
                                </button>
                                <a href="<?php echo htmlspecialchars($url); ?>" download="<?php echo htmlspecialchars($fileName); ?>" class="btn btn-sm btn-primary" title="Download" style="color:#fff !important;">
                                    <i class="fas fa-download" style="color:#fff;"></i> <span class="d-none d-sm-inline">Download</span>
                                </a>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
            <?php endif; ?>

            <?php if (!empty($supportingFiles)): ?>
            <section class="form-section no-print">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-folder-open"></i></span>
                    Supporting Documents
                </div>
                <div class="section-content">
                    <ul class="doc-list">
                        <?php foreach ($supportingFiles as $file): ?>
                        <?php
                            $fileName = $file['original_name'] ?? 'Attachment';
                            $url = $tempDirUrl . rawurlencode($file['temp_name']);
                            $isImage = isPrintableImage($fileName);
                            $isPdf = isPrintablePdf($fileName);
                            $docType = $isImage ? 'image' : ($isPdf ? 'pdf' : 'other');
                            $iconClass = $isImage ? 'fa-image' : ($isPdf ? 'fa-file-pdf' : 'fa-file');
                            $iconColor = $isImage ? '#10b981' : ($isPdf ? '#ef4444' : '#6b7280');
                        ?>
                        <li class="doc-item">
                            <div class="doc-info">
                                <i class="fas <?php echo $iconClass; ?>" style="font-size:24px;color:<?php echo $iconColor; ?>;flex-shrink:0;"></i>
                                <div style="min-width:0;">
                                    <div style="font-weight:500;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:300px;">
                                        <?php echo htmlspecialchars($fileName); ?>
                                    </div>
                                    <div style="font-size:12px;color:#6b7280;">
                                        <?php echo number_format(($file['size'] ?? 0) / 1024, 1); ?> KB
                                    </div>
                                </div>
                            </div>
                            <div class="doc-actions">
                                <button type="button" class="btn btn-sm btn-outline doc-modal-btn" data-url="<?php echo htmlspecialchars($url); ?>" data-type="<?php echo $docType; ?>" data-name="<?php echo htmlspecialchars($fileName); ?>" title="View in popup">
                                    <i class="fas fa-expand"></i> View
                                </button>
                                <button type="button" class="btn btn-sm btn-outline doc-print-btn" data-url="<?php echo htmlspecialchars($url); ?>" data-type="<?php echo $docType; ?>" data-name="<?php echo htmlspecialchars($fileName); ?>" data-category="<?php echo htmlspecialchars($file['category'] ?? 'supporting'); ?>" title="Print">
                                    <i class="fas fa-print"></i>
                                </button>
                                <a href="<?php echo htmlspecialchars($url); ?>" download="<?php echo htmlspecialchars($fileName); ?>" class="btn btn-sm btn-primary" title="Download" style="color:#fff !important;">
                                    <i class="fas fa-download" style="color:#fff;"></i> <span class="d-none d-sm-inline">Download</span>
                                </a>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
            <?php endif; ?>
        <?php else: ?>
            <!-- STANDARD MODE: Official form preview -->
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
                    <div class="field-box complainant-name-box"><?php echo htmlspecialchars($data['name_pangalan'] ?? ''); ?></div>
                    <div class="field-box complainant-address-box"><?php echo htmlspecialchars($data['address_tirahan'] ?? ''); ?></div>
                    <div class="field-box complainant-contact-box"><?php echo htmlspecialchars($data['contact_number'] ?? ''); ?></div>
                    <div class="field-box complainant-email-box"><?php echo htmlspecialchars($data['email_address'] ?? ''); ?></div>
                    
                    <!-- Involved Person/Office -->
                    <div class="field-box involved-name-box"><?php echo htmlspecialchars($data['involved_full_name'] ?? ''); ?></div>
                    <div class="field-box involved-position-box"><?php echo htmlspecialchars($data['involved_position'] ?? ''); ?></div>
                    <div class="field-box involved-address-box"><?php echo htmlspecialchars($data['involved_address'] ?? ''); ?></div>
                    <div class="field-box involved-school-box"><?php echo htmlspecialchars($data['involved_school_office_unit'] ?? ''); ?></div>
                    
                    <!-- Narration (Multi-line, Controlled) -->
                    <div class="field-box narration-box"><?php echo htmlspecialchars($data['narration_complaint'] ?? ''); ?></div>
                    
                    <!-- Signature -->
                    <div class="field-box signature-box"><?php echo htmlspecialchars($data['typed_signature'] ?? ($data['name_pangalan'] ?? '')); ?></div>
                    
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
            <?php
                // Separate files by category for standard mode
                $tempDirUrl = 'uploads/temp/';
                $stdValidIdFiles = array_filter($files, function($f) {
                    return isset($f['category']) && $f['category'] === 'valid_id';
                });
                $stdSupportingFiles = array_filter($files, function($f) {
                    $cat = $f['category'] ?? 'supporting';
                    return $cat === 'supporting';
                });
            ?>
            <?php if (!empty($stdValidIdFiles)): ?>
            <section class="form-section no-print" style="margin-top:15px;">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-id-card"></i></span>
                    Valid ID / Credentials
                </div>
                <div class="section-content">
                    <ul class="doc-list">
                        <?php foreach ($stdValidIdFiles as $file): ?>
                        <?php
                            $fileName = $file['original_name'] ?? 'Attachment';
                            $url = $tempDirUrl . rawurlencode($file['temp_name']);
                            $isImage = isPrintableImage($fileName);
                            $isPdf = isPrintablePdf($fileName);
                            $docType = $isImage ? 'image' : ($isPdf ? 'pdf' : 'other');
                            $iconClass = $isImage ? 'fa-image' : ($isPdf ? 'fa-file-pdf' : 'fa-file');
                            $iconColor = $isImage ? '#10b981' : ($isPdf ? '#ef4444' : '#6b7280');
                        ?>
                        <li class="doc-item">
                            <div class="doc-info">
                                <i class="fas <?php echo $iconClass; ?>" style="font-size:24px;color:<?php echo $iconColor; ?>;flex-shrink:0;"></i>
                                <div style="min-width:0;">
                                    <div style="font-weight:500;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:300px;">
                                        <?php echo htmlspecialchars($fileName); ?>
                                    </div>
                                    <div style="font-size:12px;color:#6b7280;">
                                        <?php echo number_format(($file['size'] ?? 0) / 1024, 1); ?> KB
                                    </div>
                                </div>
                            </div>
                            <div class="doc-actions">
                                <button type="button" class="btn btn-sm btn-outline doc-modal-btn" data-url="<?php echo htmlspecialchars($url); ?>" data-type="<?php echo $docType; ?>" data-name="<?php echo htmlspecialchars($fileName); ?>" title="View in popup">
                                    <i class="fas fa-expand"></i> View
                                </button>
                                <button type="button" class="btn btn-sm btn-outline doc-print-btn" data-url="<?php echo htmlspecialchars($url); ?>" data-type="<?php echo $docType; ?>" data-name="<?php echo htmlspecialchars($fileName); ?>" data-category="<?php echo htmlspecialchars($file['category'] ?? 'valid_id'); ?>" title="Print">
                                    <i class="fas fa-print"></i>
                                </button>
                                <a href="<?php echo htmlspecialchars($url); ?>" download="<?php echo htmlspecialchars($fileName); ?>" class="btn btn-sm btn-primary" title="Download" style="color:#fff !important;">
                                    <i class="fas fa-download" style="color:#fff;"></i> <span class="d-none d-sm-inline">Download</span>
                                </a>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
            <?php endif; ?>
            <?php if (!empty($stdSupportingFiles)): ?>
            <section class="form-section no-print" style="margin-top:15px;">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-paperclip"></i></span>
                    Supporting Documents
                </div>
                <div class="section-content">
                    <ul class="doc-list">
                        <?php foreach ($stdSupportingFiles as $file): ?>
                        <?php
                            $fileName = $file['original_name'] ?? 'Attachment';
                            $url = $tempDirUrl . rawurlencode($file['temp_name']);
                            $isImage = isPrintableImage($fileName);
                            $isPdf = isPrintablePdf($fileName);
                            $docType = $isImage ? 'image' : ($isPdf ? 'pdf' : 'other');
                            $iconClass = $isImage ? 'fa-image' : ($isPdf ? 'fa-file-pdf' : 'fa-file');
                            $iconColor = $isImage ? '#10b981' : ($isPdf ? '#ef4444' : '#6b7280');
                        ?>
                        <li class="doc-item">
                            <div class="doc-info">
                                <i class="fas <?php echo $iconClass; ?>" style="font-size:24px;color:<?php echo $iconColor; ?>;flex-shrink:0;"></i>
                                <div style="min-width:0;">
                                    <div style="font-weight:500;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:300px;">
                                        <?php echo htmlspecialchars($fileName); ?>
                                    </div>
                                    <div style="font-size:12px;color:#6b7280;">
                                        <?php echo number_format(($file['size'] ?? 0) / 1024, 1); ?> KB
                                    </div>
                                </div>
                            </div>
                            <div class="doc-actions">
                                <button type="button" class="btn btn-sm btn-outline doc-modal-btn" data-url="<?php echo htmlspecialchars($url); ?>" data-type="<?php echo $docType; ?>" data-name="<?php echo htmlspecialchars($fileName); ?>" title="View in popup">
                                    <i class="fas fa-expand"></i> View
                                </button>
                                <button type="button" class="btn btn-sm btn-outline doc-print-btn" data-url="<?php echo htmlspecialchars($url); ?>" data-type="<?php echo $docType; ?>" data-name="<?php echo htmlspecialchars($fileName); ?>" data-category="<?php echo htmlspecialchars($file['category'] ?? 'supporting'); ?>" title="Print">
                                    <i class="fas fa-print"></i>
                                </button>
                                <a href="<?php echo htmlspecialchars($url); ?>" download="<?php echo htmlspecialchars($fileName); ?>" class="btn btn-sm btn-primary" title="Download" style="color:#fff !important;">
                                    <i class="fas fa-download" style="color:#fff;"></i> <span class="d-none d-sm-inline">Download</span>
                                </a>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
            <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php 
        // Check if bypass mode is active
        $needsComplainantInfo = $isHandwritten && (empty($data['name_pangalan']) || empty($data['email_address']) || empty($data['contact_number']));
        ?>
        
        <!-- Form wrapper - includes both complainant info inputs and action buttons -->
        <form method="POST" class="no-print" id="submitForm">
        
        <?php if ($needsComplainantInfo): ?>
        <!-- Bypass Mode: Collect Complainant Information -->
        <section class="form-section" style="margin-top:20px; border: 2px solid var(--warning-color); background: #fff8e6;">
            <div class="section-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <span class="section-icon"><i class="fas fa-user-circle"></i></span>
                Complainant Information
            </div>
            <div class="section-content">
                <div style="margin-bottom: 1.5rem; color: #92400e;">
                    <p style="margin: 0;"><strong><i class="fas fa-info-circle"></i> Important:</strong> Since you uploaded a completed form, please provide your contact information so we can send you confirmation and updates about your complaint.</p>
                </div>
                
                <div class="bypass-contact-grid">
                    <!-- Name Field -->
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; color: #92400e;">
                            Full Name <span class="required" style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="bypass_name" 
                               name="bypass_name" 
                               placeholder="Your full name" 
                               value="<?php echo htmlspecialchars($data['name_pangalan'] ?? ''); ?>"
                               required>
                        <div class="field-error" id="bypassNameError" style="color: #dc2626; font-size: 0.875rem; margin-top: 0.5rem; display: none;">
                            <i class="fas fa-exclamation-circle"></i> Name is required.
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; color: #92400e;">
                            Email Address <span class="required" style="color: #dc2626;">*</span>
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="bypass_email" 
                               name="bypass_email" 
                               placeholder="your@email.com" 
                               value="<?php echo htmlspecialchars($data['email_address'] ?? ''); ?>"
                               required>
                        <div class="field-error" id="bypassEmailError" style="color: #dc2626; font-size: 0.875rem; margin-top: 0.5rem; display: none;">
                            <i class="fas fa-exclamation-circle"></i> Valid email required.
                        </div>
                    </div>

                    <!-- Contact Number Field -->
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; color: #92400e;">
                            Contact Number <span class="required" style="color: #dc2626;">*</span>
                        </label>
                        <input type="tel" 
                               class="form-control" 
                               id="bypass_contact" 
                               name="bypass_contact" 
                               placeholder="09171234567" 
                               value="<?php echo htmlspecialchars($data['contact_number'] ?? ''); ?>"
                               required>
                        <div class="field-error" id="bypassContactError" style="color: #dc2626; font-size: 0.875rem; margin-top: 0.5rem; display: none;">
                            <i class="fas fa-exclamation-circle"></i> Contact number required (min 11 digits).
                        </div>
                    </div>
                </div>
                
                <!-- Unit Selection - Full Width Below -->
                <div style="margin-top: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; color: #92400e;">
                            Complaint Recipient Unit <span class="required" style="color: #dc2626;">*</span>
                        </label>
                        <select class="form-control" 
                                id="bypass_unit" 
                                name="bypass_unit" 
                                required
                                style="max-width: 100%;">
                            <option value="">-- Select Unit/Office --</option>
                            <option value="SDS" <?php echo ($data['involved_school_office_unit'] ?? '') === 'SDS' ? 'selected' : ''; ?>>SDS: Schools Division Superintendent</option>
                            <option value="ASDS" <?php echo ($data['involved_school_office_unit'] ?? '') === 'ASDS' ? 'selected' : ''; ?>>ASDS: Assistant Schools Division Superintendent</option>
                            <option value="Admin" <?php echo ($data['involved_school_office_unit'] ?? '') === 'Admin' ? 'selected' : ''; ?>>Admin: Cash, Personnel, Records, Supply, General Services, Procurement</option>
                            <option value="CID" <?php echo ($data['involved_school_office_unit'] ?? '') === 'CID' ? 'selected' : ''; ?>>CID: Curriculum Implementation Division (LRMS, Instructional Management, PSDS)</option>
                            <option value="Finance" <?php echo ($data['involved_school_office_unit'] ?? '') === 'Finance' ? 'selected' : ''; ?>>Finance: Accounting, Budget</option>
                            <option value="ICTO" <?php echo ($data['involved_school_office_unit'] ?? '') === 'ICTO' ? 'selected' : ''; ?>>Information and Communication Technology Office</option>
                            <option value="Legal" <?php echo ($data['involved_school_office_unit'] ?? '') === 'Legal' ? 'selected' : ''; ?>>Legal Office</option>
                            <option value="SGOD" <?php echo ($data['involved_school_office_unit'] ?? '') === 'SGOD' ? 'selected' : ''; ?>>SGOD: School Governance and Operations Division (M&E, SocMob, Planning & Research, HRD, Facilities, School Health)</option>
                        </select>
                        <div class="field-error" id="bypassUnitError" style="color: #dc2626; font-size: 0.875rem; margin-top: 0.5rem; display: none;">
                            <i class="fas fa-exclamation-circle"></i> Please select the unit/office for this complaint.
                        </div>
                        <small style="color: #78716c; margin-top: 0.5rem; display: block;">Select the unit/office where your complaint should be directed.</small>
                    </div>
                </div>
            </div>
        </section>
        <?php else: ?>
        <!-- Standard Mode: Display Complainant Information -->
        <section class="form-section" style="margin-top:20px; background: #ffffff; border: 1px solid #1b4a9a;">
            <div class="section-header" style="background: #1b4a9a; border: none; color: #ffffff; padding: 12px 15px;">
                <span class="section-icon"><i class="fas fa-info-circle"></i></span>
                <strong>Complainant Information</strong>
            </div>
            <div class="section-content">
                <table class="complainant-info-table" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #1b4a9a; background: #ffffff; color: #1b4a9a; width: 25%;"><strong>Name:</strong></td>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #1b4a9a; background: #ffffff;">
                            <?php echo htmlspecialchars($data['name_pangalan'] ?? 'Not provided'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #1b4a9a; background: #ffffff; color: #1b4a9a;"><strong>Email:</strong></td>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #1b4a9a; background: #ffffff;">
                            <?php echo htmlspecialchars($data['email_address'] ?? 'Not provided'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #1b4a9a; background: #ffffff; color: #1b4a9a;"><strong>Contact:</strong></td>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #1b4a9a; background: #ffffff;">
                            <?php echo htmlspecialchars($data['contact_number'] ?? 'Not provided'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 12px; background: #ffffff; color: #1b4a9a;"><strong>Complaint Recipient:</strong></td>
                        <td style="padding: 10px 12px; background: #ffffff;">
                            <?php 
                            $unitNames = [
                                'SDS' => 'SDS: Schools Division Superintendent',
                                'ASDS' => 'ASDS: Assistant Schools Division Superintendent',
                                'Admin' => 'Admin: Cash, Personnel, Records, Supply, General Services, Procurement',
                                'CID' => 'CID: Curriculum Implementation Division (LRMS, Instructional Management, PSDS)',
                                'Finance' => 'Finance: Accounting, Budget',
                                'ICTO' => 'Information and Communication Technology Office',
                                'Legal' => 'Legal Office',
                                'SGOD' => 'SGOD: School Governance and Operations Division (M&E, SocMob, Planning & Research, HRD, Facilities, School Health)'
                            ];
                            $unitCode = $data['involved_school_office_unit'] ?? '';
                            echo htmlspecialchars($unitNames[$unitCode] ?? ($unitCode ?: 'Not specified'));
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </section>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="form-actions" style="margin-top:20px;">
            <div class="review-action-group" style="display:flex;gap:10px;">
                <a href="index.php?edit=1" class="btn btn-secondary">⬅️ Go Back & Edit</a>
                <button type="button" 
                        class="btn btn-outline" 
                        onclick="window.print();"
                        style="border-color:#1b4a9a;color:#1b4a9a;">
                    🖨️ Print Form
                </button>
            </div>
            <div class="review-action-group" style="display:flex;gap:10px;">
                <button type="submit" 
                        name="confirm_submit" 
                        class="btn btn-success btn-lg" 
                        id="submitBtn">
                    ✅ Confirm & Submit
                </button>
            </div>
        </div>
        </form>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isBypassMode = <?php echo $isHandwritten ? 'true' : 'false'; ?>;
            const needsComplainantInfo = <?php echo $needsComplainantInfo ? 'true' : 'false'; ?>;
            
            let bypassNameInput, bypassEmailInput, bypassContactInput, bypassUnitSelect;
            let bypassNameError, bypassEmailError, bypassContactError, bypassUnitError;
            
            if (isBypassMode && needsComplainantInfo) {
                bypassNameInput = document.getElementById('bypass_name');
                bypassEmailInput = document.getElementById('bypass_email');
                bypassContactInput = document.getElementById('bypass_contact');
                bypassUnitSelect = document.getElementById('bypass_unit');
                bypassNameError = document.getElementById('bypassNameError');
                bypassEmailError = document.getElementById('bypassEmailError');
                bypassContactError = document.getElementById('bypassContactError');
                bypassUnitError = document.getElementById('bypassUnitError');
            }
            
            const submitBtn = document.getElementById('submitBtn');
            const submitForm = document.getElementById('submitForm');
            
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
            
            function validateContact(contact) {
                const digits = contact.replace(/\D/g, '');
                return digits.length >= 7;
            }
            
            function updateSubmitButton() {
                if (!isBypassMode || !needsComplainantInfo) {
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                    return;
                }
                
                const name = bypassNameInput.value.trim();
                const email = bypassEmailInput.value.trim();
                const contact = bypassContactInput.value.trim();
                const unit = bypassUnitSelect.value;
                
                // Clear previous errors
                bypassNameError.style.display = 'none';
                bypassEmailError.style.display = 'none';
                bypassContactError.style.display = 'none';
                bypassUnitError.style.display = 'none';
                
                // Validate name
                const nameValid = name.length >= 2;
                if (name.length > 0 && name.length < 2) {
                    bypassNameError.style.display = 'block';
                }
                
                // Validate email
                const emailValid = validateEmail(email);
                if (email.length > 0 && !emailValid) {
                    bypassEmailError.style.display = 'block';
                }
                
                // Validate contact
                const contactValid = validateContact(contact);
                if (contact.length > 0 && !contactValid) {
                    bypassContactError.style.display = 'block';
                }
                
                // Validate unit
                const unitValid = unit !== '';
                
                // Enable/disable submit button
                const allValid = nameValid && emailValid && contactValid && unitValid;
                
                if (allValid) {
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                } else {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                    submitBtn.style.cursor = 'not-allowed';
                }
            }
            
            // Add event listeners for bypass mode
            if (isBypassMode && needsComplainantInfo) {
                bypassNameInput.addEventListener('input', updateSubmitButton);
                bypassNameInput.addEventListener('blur', updateSubmitButton);
                bypassEmailInput.addEventListener('input', updateSubmitButton);
                bypassEmailInput.addEventListener('blur', updateSubmitButton);
                bypassContactInput.addEventListener('input', updateSubmitButton);
                bypassContactInput.addEventListener('blur', updateSubmitButton);
                bypassUnitSelect.addEventListener('change', updateSubmitButton);
            }
            
            // Prevent form submission if validation fails
            submitForm.addEventListener('submit', function(e) {
                if (!isBypassMode || !needsComplainantInfo) {
                    return;
                }
                
                const name = bypassNameInput.value.trim();
                const email = bypassEmailInput.value.trim();
                const contact = bypassContactInput.value.trim();
                const unit = bypassUnitSelect.value;
                
                const isValid = name.length >= 2 && validateEmail(email) && validateContact(contact) && unit !== '';
                
                if (!isValid) {
                    e.preventDefault();
                    updateSubmitButton();
                    if (unit === '') {
                        bypassUnitError.style.display = 'block';
                    }
                    bypassNameInput.focus();
                }
            });
            
            // Initial validation
            updateSubmitButton();
        });
        </script>

        <!-- Document Viewer Modal -->
        <div id="docViewerModal" class="doc-viewer-modal">
            <div class="doc-modal-overlay"></div>
            <div class="doc-modal-container">
                <div class="doc-modal-header">
                    <h3 id="docModalTitle">Document Preview</h3>
                    <div class="doc-modal-actions">
                        <div id="modalZoomToolbar" style="display:flex;align-items:center;gap:6px;margin-right:12px;">
                            <span style="font-size:12px;color:#666;">Zoom:</span>
                            <button type="button" class="btn btn-sm btn-outline" data-zoom="out" title="Zoom out">−</button>
                            <button type="button" class="btn btn-sm btn-outline" data-zoom="in" title="Zoom in">+</button>
                            <button type="button" class="btn btn-sm btn-secondary" data-zoom="reset" title="Reset zoom">Reset</button>
                            <span id="modalZoomLabel" style="font-size:12px;min-width:40px;">100%</span>
                        </div>
                        <a id="modalDownloadBtn" href="#" download class="btn btn-sm btn-primary" title="Download">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <button type="button" id="docModalClose" class="btn btn-sm btn-secondary" title="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div id="docModalContent" class="doc-modal-content">
                    <!-- Document content will be loaded here -->
                </div>
            </div>
        </div>

        <style>
        /* Document Viewer Modal Styles */
        .doc-viewer-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
        }

        .doc-viewer-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .doc-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.28);
            backdrop-filter: blur(2px);
        }

        .doc-modal-container {
            position: relative;
            width: 95%;
            max-width: 1200px;
            height: 90vh;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.18);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .doc-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
            flex-shrink: 0;
        }

        .doc-modal-header h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 300px;
        }

        .doc-modal-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .doc-modal-content {
            flex: 1;
            overflow: auto;
            padding: 20px;
            background: #f3f4f6;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        .doc-modal-content img {
            max-width: 100%;
            height: auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        .modal-doc-inner {
            transition: transform 0.2s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .doc-modal-header {
                flex-wrap: wrap;
                gap: 12px;
            }
            
            .doc-modal-header h3 {
                width: 100%;
                max-width: none;
            }
            
            .doc-modal-actions {
                width: 100%;
                justify-content: flex-end;
                flex-wrap: wrap;
            }
            
            #modalZoomToolbar {
                display: none !important;
            }
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalBtns = document.querySelectorAll('.doc-modal-btn');
            const printBtns = document.querySelectorAll('.doc-print-btn');
            const docModal = document.getElementById('docViewerModal');
            const docModalTitle = document.getElementById('docModalTitle');
            const docModalContent = document.getElementById('docModalContent');
            const docModalClose = document.getElementById('docModalClose');
            const docModalOverlay = document.querySelector('.doc-modal-overlay');
            const modalZoomToolbar = document.getElementById('modalZoomToolbar');
            const modalZoomLabel = document.getElementById('modalZoomLabel');
            const modalDownloadBtn = document.getElementById('modalDownloadBtn');
            let currentDocUrl = '';
            let currentDocName = '';
            let currentDocType = '';
            let modalZoom = 1;
            const MIN_ZOOM = 0.25;
            const MAX_ZOOM = 4;
            const ZOOM_STEP = 0.25;

            function getCategoryLabel(category) {
                if (category === 'handwritten_form') {
                    return 'Uploaded Completed Complaint-Assisted Form';
                }
                if (category === 'valid_id') {
                    return 'Valid ID / Credentials';
                }
                return 'Supporting Document';
            }

            function printAttachment(url, type, category) {
                const categoryLabel = getCategoryLabel(category);
                const iframe = document.createElement('iframe');
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                iframe.style.visibility = 'hidden';
                document.body.appendChild(iframe);

                const cleanup = () => {
                    if (iframe && iframe.parentNode) {
                        iframe.parentNode.removeChild(iframe);
                    }
                };

                const scheduleCleanup = () => {
                    setTimeout(cleanup, 8000);
                };

                if (type === 'pdf') {
                    iframe.onload = () => {
                        try {
                            iframe.contentWindow.focus();
                            iframe.contentWindow.print();
                        } catch (e) {
                            window.print();
                        }
                        if (typeof window.onafterprint !== 'function') {
                            window.onafterprint = cleanup;
                        }
                        scheduleCleanup();
                    };
                    iframe.src = url;
                    return;
                }

                const doc = iframe.contentDocument || iframe.contentWindow.document;
                doc.open();
                if (type === 'image') {
                    doc.write(`
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Print Document</title>
                            <style>
                                body { margin: 0; color: #111827; font-family: Arial, sans-serif; }
                                .print-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-bottom: 2px solid #111827; }
                                .print-header img { height: 48px; width: auto; }
                                .print-header .sdo-logo { border-radius: 50%; }
                                .print-title { text-align: center; flex: 1; font-size: 14px; font-weight: 700; text-transform: uppercase; }
                                .print-subtitle { text-align: center; font-size: 12px; font-weight: 600; margin-top: 4px; }
                                .print-body { padding: 18px 24px; }
                                .category-label { font-size: 12px; font-weight: 600; margin-bottom: 10px; text-transform: uppercase; }
                                .doc-image { width: 100%; height: auto; display: block; border: 1px solid #e5e7eb; }
                                @media print { body { margin: 0; } img { max-width: 100%; height: auto; } }
                            </style>
                        </head>
                        <body>
                            <div class="print-header">
                                <img src="/CTS/assets/img/sdo-logo.jpg" alt="SDO Logo" class="sdo-logo">
                                <div>
                                    <div class="print-title">Schools Division office of San Pedro City</div>
                                    <div class="print-subtitle">${categoryLabel}</div>
                                </div>
                                <img src="/CTS/assets/img/bagongpilpinas-logo.png" alt="Bagong Pilipinas">
                            </div>
                            <div class="print-body">
                                <div class="category-label">${categoryLabel}</div>
                                <img class="doc-image" src="${url}" onload="window.print(); window.onafterprint = function(){ parent.postMessage('print-done','*'); }" />
                            </div>
                        </body>
                        </html>
                    `);
                } else {
                    doc.write(`
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Print Document</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 40px; color: #111827; }
                                a { color: #1b4a9a; }
                            </style>
                        </head>
                        <body>
                            <p>This file type cannot be previewed for printing.</p>
                            <p><a href="${url}" target="_blank">Open file</a> and print from the viewer.</p>
                        </body>
                        </html>
                    `);
                }
                doc.close();

                const onMessage = (event) => {
                    if (event.data === 'print-done') {
                        window.removeEventListener('message', onMessage);
                        cleanup();
                    }
                };
                window.addEventListener('message', onMessage);
                if (typeof window.onafterprint !== 'function') {
                    window.onafterprint = cleanup;
                }
                scheduleCleanup();
            }


            function applyModalZoom() {
                const inner = docModalContent.querySelector('.modal-doc-inner');
                if (inner) {
                    inner.style.transform = 'scale(' + modalZoom + ')';
                    inner.style.transformOrigin = 'top left';
                }
                if (modalZoomLabel) {
                    modalZoomLabel.textContent = Math.round(modalZoom * 100) + '%';
                }
            }

            function openDocModal(url, type, name) {
                currentDocUrl = url;
                currentDocName = name;
                currentDocType = type;
                docModalTitle.textContent = name || 'Document Preview';

                let contentHtml = '';
                if (type === 'image') {
                    contentHtml = '<div class="modal-doc-inner" style="transform-origin:top left;"><img src="' + url + '" alt="' + (name || 'Document') + '" style="max-width:100%;height:auto;display:block;"></div>';
                } else if (type === 'pdf') {
                    contentHtml = '<div class="modal-doc-inner" style="width:100%;height:100%;"><embed src="' + url + '" type="application/pdf" style="width:100%;height:100%;min-height:70vh;border:none;" /></div>';
                } else {
                    contentHtml = '<div class="modal-doc-inner" style="text-align:center;padding:40px;">' +
                        '<i class="fas fa-file" style="font-size:4rem;color:#6b7280;margin-bottom:20px;"></i>' +
                        '<p style="margin-bottom:20px;font-size:1.1rem;">Preview not available for this file type.</p>' +
                        '<p style="color:#666;">Use the download button above to view this file.</p>' +
                        '</div>';
                }

                docModalContent.innerHTML = contentHtml;
                modalZoom = 1;
                applyModalZoom();

                if (modalDownloadBtn) {
                    modalDownloadBtn.href = url;
                    modalDownloadBtn.download = name;
                }

                docModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeDocModal() {
                docModal.classList.remove('active');
                document.body.style.overflow = '';
            }


            modalBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const url = this.getAttribute('data-url');
                    const type = this.getAttribute('data-type');
                    const name = this.getAttribute('data-name');
                    openDocModal(url, type, name);
                });
            });

            printBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const url = this.getAttribute('data-url');
                    const type = this.getAttribute('data-type');
                    const category = this.getAttribute('data-category') || 'supporting';
                    printAttachment(url, type, category);
                });
            });


            if (docModalClose) {
                docModalClose.addEventListener('click', closeDocModal);
            }
            if (docModalOverlay) {
                docModalOverlay.addEventListener('click', closeDocModal);
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && docModal.classList.contains('active')) {
                    closeDocModal();
                }
            });

            if (modalZoomToolbar) {
                modalZoomToolbar.addEventListener('click', function(e) {
                    const btn = e.target.closest('button[data-zoom]');
                    if (!btn) return;
                    const action = btn.getAttribute('data-zoom');
                    if (action === 'in') {
                        modalZoom = Math.min(MAX_ZOOM, modalZoom + ZOOM_STEP);
                    } else if (action === 'out') {
                        modalZoom = Math.max(MIN_ZOOM, modalZoom - ZOOM_STEP);
                    } else if (action === 'reset') {
                        modalZoom = 1;
                    }
                    applyModalZoom();
                });
            }
        });
        </script>

        <footer class="form-footer no-print">
            <p>DepEd — Schools Division Office of San Pedro City</p>
            <span>&copy; <?php echo date('Y'); ?> ICT Unit</span>
        </footer>
    </div>
</body>
</html>
