<?php
/**
 * View Complaint Details Page
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../models/ComplaintAdmin.php';

$auth = auth();
$auth->requirePermission('complaints.view');

$complaintModel = new ComplaintAdmin();

// Get complaint ID
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /SDO-cts/admin/complaints.php');
    exit;
}

// Get complaint details
$complaint = $complaintModel->getById($id);
if (!$complaint) {
    header('Location: /SDO-cts/admin/complaints.php?error=not_found');
    exit;
}

// Log view action
$auth->logActivity('view', 'complaint', $id, 'Viewed complaint ' . $complaint['reference_number']);

// Get related data
$documents = $complaintModel->getDocuments($id);
$history = $complaintModel->getStatusHistory($id);
$assignments = $complaintModel->getAssignments($id);

// Primary uploaded document (first attachment), used for uploaded-form complaints
$primaryDoc = null;
$primaryUrl = null;
$primaryExt = null;
$primaryOriginalName = null;
if (!empty($documents)) {
    $primaryDoc = $documents[0];
    // Use relative path from file_path column, fallback to old structure for backward compatibility
    $primaryUrl = !empty($primaryDoc['file_path']) ? "/SDO-cts/" . $primaryDoc['file_path'] : "/SDO-cts/uploads/complaints/" . $complaint['id'] . "/" . $primaryDoc['file_name'];
    $primaryExt = strtolower(pathinfo($primaryDoc['file_name'], PATHINFO_EXTENSION));
    $primaryOriginalName = $primaryDoc['original_name'] ?? null;
}

// Status config
$statusConfig = STATUS_CONFIG;
$statusWorkflow = STATUS_WORKFLOW;

// Determine if this complaint came from an uploaded completed form
// Primary flag is signature_type = 'uploaded_form' (new flow).
// As a safety net for older/edge records, also treat it as uploaded-form
// when core complainant fields are blank but there is at least one document.
// Note: email_address is excluded from this check since bypass mode now captures email separately.
$hasCoreFieldsEmpty =
    empty(trim($complaint['name_pangalan'] ?? '')) &&
    empty(trim($complaint['address_tirahan'] ?? '')) &&
    empty(trim($complaint['contact_number'] ?? '')) &&
    empty(trim($complaint['narration_complaint'] ?? ''));

$isUploadedForm = (($complaint['signature_type'] ?? '') === 'uploaded_form')
    || ($hasCoreFieldsEmpty && !empty($documents));


include __DIR__ . '/includes/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    /* Form Container - Fixed size matching the official form */
    .form-container {
        position: relative;
        width: 850px;
        max-width: 100%;
        margin: 0 auto;
        background: #fff;
        box-shadow: 0 1px 6px rgba(0,0,0,0.08);
        /* Keep the entire template together on one page unless narration adds page 2 */
        page-break-after: avoid;
        page-break-inside: avoid;
        break-after: avoid;
        break-inside: avoid;
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

    /* CTS Ticket Number Field */
    .cts-ticket-box {
        top: 3.0%;
        left: 10%;
        width: 35%;
        height: 2%;
        font-size: 16px;
        font-weight: bold;
        color: #c00;
    }

    /* Checkmark Fields - precisely positioned in checkbox squares */
    .check-osds    { top: 5.65%; left: 52.8%; font-size: 18px; font-weight: bold; letter-spacing: 0.03em; }
    .check-sgod    { top: 5.65%; left: 62.05%; font-size: 18px; font-weight: bold; letter-spacing: 0.03em; }
    .check-cid     { top: 7.85%; left: 52.6%; font-size: 18px; font-weight: bold; letter-spacing: 0.03em; }
    .check-others  { top: 7.65%; left: 62.10%; font-size: 18px; font-weight: bold; letter-spacing: 0.03em; }

    /* Others Text Field */
    .others-text-box {
        top: 7.65%;
        left: 80%;
        width: 29%;
        height: 2%;
        font-size: 15px;
    }
    
    /* Date Field - on the Date/Petsa line */
    .date-box {
        top: 12.7%;
        left: 67%;
        width: 38%;
        height: 1.8%;
        font-size: 16px;
    }
    
    /* Complainant Name */
    .complainant-name-box {
        top: 31.5%;
        left: 24%;
        width: 66%;
        height: 1.5%;
        font-size: 15px;
    }
    
    /* Complainant Address */
    .complainant-address-box {
        top: 33.2%;
        left: 24%;
        width: 66%;
        height: 1.5%;
        font-size: 15px;
    }
    
    /* Complainant Contact */
    .complainant-contact-box {
        top: 34.9%;
        left: 24%;
        width: 66%;
        height: 1.5%;
        font-size: 15px;
    }
    
    /* Complainant Email */
    .complainant-email-box {
        top: 36.6%;
        left: 24%;
        width: 66%;
        height: 1.5%;
        font-size: 15px;
    }
    
    /* Involved Person Name */
    .involved-name-box {
        top: 42.1%;
        left: 24%;
        width: 73%;
        height: 1.5%;
        font-size: 15px;
    }
    
    /* Involved Position */
    .involved-position-box {
        top: 44.0%;
        left: 24%;
        width: 73%;
        height: 1.5%;
        font-size: 15px;
    }
    
    /* Involved Address */
    .involved-address-box {
        top: 45.7%;
        left: 24%;
        width: 73%;
        height: 1.5%;
        font-size: 15px;
    }
    
    /* Involved School/Office */
    .involved-school-box {
        top: 47.4%;
        left: 24%;
        width: 69%;
        height: 1.5%;
        font-size: 15px;
    }
    
    /* Narration Box - Multi-line with controlled height */
    .narration-box {
        top: 55.5%;
        left: 10%;
        width: 80%;
        height: 15%;
        font-size: 14px;
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
        font-size: 20px;
        text-align: center;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Admin View Header */
    .admin-view-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .admin-view-header .back-link {
        color: #666;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .admin-view-header .back-link:hover {
        color: #333;
    }
    
    .header-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .reference-badge {
        background: linear-gradient(135deg, #1b4a9a, #1b4a9a);
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
    }
    
    .attached-notice {
        background: #f5f5f5;
        padding: 12px 15px;
        margin-top: 15px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }
    
    .attached-notice ul {
        margin: 8px 0 0 20px;
        padding: 0;
    }
    
    .attached-notice a {
        color: #1b4a9a;
        text-decoration: none;
    }
    
    .attached-notice a:hover {
        text-decoration: underline;
    }
    
    /* Additional Page Styles for Admin - Official Form Look */
    .additional-page {
        position: relative;
        width: 850px;
        max-width: 100%;
        margin: 30px auto 0;
        background: #fff;
        box-shadow: 0 1px 6px rgba(0,0,0,0.08);
        padding: 30px 40px;
        min-height: 1100px;
        page-break-before: always;
        page-break-inside: avoid;
        break-before: page;
        break-inside: avoid;
        clear: both;
        display: block;
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
        font-size: 15px;
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
    
    @page {
        size: A4;
        margin: 0;
    }
    
    @media print {
        /* Reset body */
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Hide admin UI elements */
        .sidebar,
        .top-bar,
        .admin-footer,
        .admin-view-header,
        .complaint-sidebar,
        .attached-notice,
        .no-print,
        .modal-overlay {
            display: none !important;
        }
        
        /* Reset layout containers */
        .admin-layout {
            display: block !important;
        }
        
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        
        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        
        .complaint-detail-grid {
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .complaint-main {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }
        
        /* Form container styling for print */
        .form-container {
            width: 720px !important;
            max-width: 720px !important;
            margin: 0 auto !important;
            box-shadow: none !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
            break-after: avoid !important;
            break-inside: avoid !important;
        }
        
        .form-background {
            width: 100% !important;
            height: auto !important;
        }
        
        /* Additional page print styling */
        .additional-page {
            width: 850px !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            padding: 30px 40px !important;
            box-shadow: none !important;
            page-break-before: always !important;
            break-before: page !important;
            position: relative !important;
            background: #fff !important;
            box-sizing: border-box !important;
            overflow: visible !important;
            /* Ensure content fits within page with proper borders */
        }
        
        /* Ensure header and content fit within the page */
        .additional-page-header,
        .additional-page-content {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        
        .additional-page-header {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            border: 1px solid #000 !important;
            border-left: 3px solid #000 !important;
            border-right: 3px solid #000 !important;
            border-bottom: none !important;
            padding: 10px !important;
            background: #fff !important;
            margin: 0 !important;
            overflow: visible !important;
        }
        
        .additional-page-content {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            border: 1px solid #000 !important;
            border-left: 3px solid #000 !important;
            border-right: 3px solid #000 !important;
            border-bottom: 3px solid #000 !important;
            padding: 5px 10px !important;
            margin: 0 !important;
            background: repeating-linear-gradient(
                transparent,
                transparent 27px,
                #000 27px,
                #000 28px
            ) !important;
            overflow: visible !important;
        }
        
        .page-indicator {
            display: none !important;
        }
    }
    
    @media (max-width: 850px) {
        .form-container {
            width: 100%;
        }
        .field-box {
            font-size: 13px;
        }
        .narration-box {
            font-size: 12px;
        }
        .signature-box {
            font-size: 18px;
        }
    }

    @media (max-width: 768px) {
        .complaint-detail-grid,
        .complaint-main,
        .complaint-sidebar {
            min-width: 0;
            width: 100%;
            max-width: 100%;
        }

        .form-container,
        .additional-page {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            overflow: hidden;
        }

        .additional-page {
            box-sizing: border-box;
            padding: 12px 8px !important;
            min-height: auto;
        }

        .additional-page-header,
        .additional-page-content {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .additional-page-header {
            padding: 8px 6px;
            border-left-width: 2px;
            border-right-width: 2px;
        }

        .additional-page-header h2 {
            font-size: 13px;
            line-height: 1.3;
        }

        .additional-page-header p {
            font-size: 11px;
            line-height: 1.35;
        }

        .additional-page-content {
            min-height: 520px;
            font-size: 13px;
            line-height: 24px;
            padding: 4px 8px;
            border-left-width: 2px;
            border-right-width: 2px;
            border-bottom-width: 2px;
            background: repeating-linear-gradient(
                transparent,
                transparent 23px,
                #000 23px,
                #000 24px
            );
        }

        .page-number-label {
            text-align: center;
            font-size: 10px;
            margin-bottom: 8px;
            overflow-wrap: anywhere;
        }

        .form-background {
            width: 100% !important;
            height: auto !important;
        }

        .page-indicator {
            font-size: 0.78rem;
        }

        .admin-view-header,
        .header-actions {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
        }

        .header-actions .btn,
        .header-actions .reference-badge,
        .header-actions .status-badge {
            width: 100%;
            justify-content: center;
            text-align: center;
        }

        .complainant-contact-card {
            flex-wrap: wrap !important;
        }

        .complainant-contact-card > a.btn {
            width: 100%;
            justify-content: center;
        }

        .attached-notice li {
            flex-wrap: wrap !important;
        }

        .attached-notice li > div:last-child {
            width: 100%;
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
        }

        .attached-notice li > div:last-child .btn {
            flex: 1 1 100%;
            justify-content: center;
        }
    }
</style>

<div class="admin-view-header no-print">
    <a href="/SDO-cts/admin/complaints.php" class="back-link">← Back to Complaints</a>
    <div class="header-actions">
        <span class="reference-badge"><?php echo htmlspecialchars($complaint['reference_number']); ?></span>
        <span class="status-badge status-<?php echo $complaint['status']; ?> large">
            <?php echo $statusConfig[$complaint['status']]['icon'] . ' ' . $statusConfig[$complaint['status']]['label']; ?>
        </span>
                    <button type="button" class="btn btn-outline" onclick="printDocument()">
                        <i class="fas fa-print"></i> Print</button>
        <?php if (!$isUploadedForm): ?>
        <a href="/SDO-cts/admin/api/generate-form.php?id=<?php echo $complaint['id']; ?>" class="btn btn-primary" title="Download filled PDF form">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="complaint-detail-grid">
    <!-- Main Content -->
    <div class="complaint-main">
        <?php if ($isUploadedForm): ?>
                <!-- UPLOADED FORM MODE: show uploaded document(s) instead of blank template -->
                <?php if (!empty($documents)): ?>
                    <?php
                    // Separate documents by category
                    $formDocs = [];      // handwritten_form category (complaint form)
                    $validIdDocs = [];   // valid_id category
                    $supportingDocs = []; // supporting category
            
                    foreach ($documents as $doc) {
                        $cat = $doc['category'] ?? 'supporting';
                        if ($cat === 'handwritten_form') {
                            $formDocs[] = $doc;
                        } elseif ($cat === 'valid_id') {
                            $validIdDocs[] = $doc;
                        } else {
                            $supportingDocs[] = $doc;
                        }
                    }

                    // Fallback for old records without category: use file type
                    // Images are complaint forms, others are attachments
                    if (empty($formDocs) && empty($validIdDocs) && empty($supportingDocs)) {
                        foreach ($documents as $doc) {
                            $ext = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                $formDocs[] = $doc;
                            } else {
                                $supportingDocs[] = $doc;
                            }
                        }
                    }

                    // Use the first form document as the primary uploaded complaint form
                    $primaryDoc = !empty($formDocs) ? $formDocs[0] : null;
                    // Use relative path from file_path column, fallback to old structure for backward compatibility
                    $primaryUrl = $primaryDoc ? (!empty($primaryDoc['file_path']) ? "/SDO-cts/" . $primaryDoc['file_path'] : "/SDO-cts/uploads/complaints/" . $complaint['id'] . "/" . $primaryDoc['file_name']) : '';
                    $primaryExt = $primaryDoc ? strtolower(pathinfo($primaryDoc['file_name'], PATHINFO_EXTENSION)) : '';
                    $primaryIsImage = in_array($primaryExt, ['jpg', 'jpeg', 'png', 'gif']);
                    $primaryIsPdf = ($primaryExt === 'pdf');
                    ?>
                    <div class="uploaded-documents-section" style="padding:20px 0;">
                        <!-- Complainant Contact Info (Email captured during bypass submission) -->
                        <?php if (!empty($complaint['email_address'])): ?>
                            <div class="complainant-contact-card" style="margin-bottom:24px;padding:16px 20px;background:linear-gradient(135deg, #1b4a9a 0%, #1b4a9a 100%);border:1px solid #1b4a9a;border-radius:10px;display:flex;align-items:center;gap:16px;">
                                <div style="width:44px;height:44px;background:linear-gradient(135deg, #1b4a9a 0%, #1b4a9a 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="	fas fa-envelope-open-text" style="color:#fff;font-size:18px;"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:12px;font-weight:600;color:#ffffff;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Complainant Email</div>
                                    <div style="font-size:15px;font-weight:500;color:#ffffff;">
                                        <a href="mailto:<?php echo htmlspecialchars($complaint['email_address']); ?>" style="color:#ffffff;text-decoration:none;">
                                            <?php echo htmlspecialchars($complaint['email_address']); ?>
                                        </a>
                                    </div>
                                </div>
                                <a href="https://mail.google.com/mail/?view=cm&to=<?php echo urlencode($complaint['email_address']); ?>" target="_blank" class="btn btn-sm btn-primary" title="Send Email via Gmail" style="color:#fff !important;">
                                    <i class="fab fa-google" style="color:#fff;"></i>
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Uploaded Complaint Form -->
                        <?php if ($primaryDoc): ?>
                            <div class="doc-category-section" style="margin-bottom:24px;">
                                <h4 style="margin:0 0 12px;font-size:14px;font-weight:600;color:#374151;display:flex;align-items:center;gap:8px;">
                                    <i class="fas fa-file-alt" style="color:#1b4a9a;"></i> Uploaded Complaint-Assisted Form:
                                </h4>
                                <ul class="doc-list" style="list-style:none;padding:0;margin:0;">
                                    <?php
                                    $fileExt = strtolower(pathinfo($primaryDoc['file_name'], PATHINFO_EXTENSION));
                                    $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    $isPdf = ($fileExt === 'pdf');
                                    $docType = $isImage ? 'image' : ($isPdf ? 'pdf' : 'other');
                                    // Use relative path from file_path column, fallback to old structure for backward compatibility
                                    $fileUrl = !empty($primaryDoc['file_path']) ? "/SDO-cts/" . $primaryDoc['file_path'] : "/SDO-cts/uploads/complaints/" . $complaint['id'] . "/" . $primaryDoc['file_name'];
                                    $iconClass = $isImage ? 'fa-image' : ($isPdf ? 'fa-file-pdf' : 'fa-file');
                                    $iconColor = $isImage ? '#10b981' : ($isPdf ? '#ef4444' : '#6b7280');
                                    ?>
                                    <li class="doc-item" style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:8px;flex-wrap:nowrap;gap:12px;">
                                        <div class="doc-info" style="display:flex;align-items:center;gap:12px;min-width:0;flex:1;overflow:hidden;">
                                            <i class="fas <?php echo $iconClass; ?>" style="font-size:24px;color:<?php echo $iconColor; ?>;flex-shrink:0;"></i>
                                            <div style="min-width:0;flex:1;overflow:hidden;">
                                                <div style="font-weight:500;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                    <?php echo htmlspecialchars($primaryDoc['original_name']); ?>
                                                </div>
                                                <div style="font-size:12px;color:#6b7280;">
                                                    <?php echo number_format($primaryDoc['file_size'] / 1024, 1); ?> KB
                                                </div>
                                            </div>
                                        </div>
                                        <div class="doc-actions" style="display:flex;align-items:center;gap:8px;flex-shrink:0;flex-grow:0;">
                                            <button type="button" class="btn btn-sm btn-outline doc-modal-btn" 
                                                    data-url="<?php echo htmlspecialchars($fileUrl); ?>" 
                                                    data-type="<?php echo $docType; ?>" 
                                                    data-name="<?php echo htmlspecialchars($primaryDoc['original_name']); ?>" 
                                                    data-doc-category="Uploaded Complaint-Assisted Form"
                                                    title="View in popup">
                                                <i class="fas fa-expand"></i> View
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline doc-print-btn" data-url="<?php echo htmlspecialchars($fileUrl); ?>" data-type="<?php echo $docType; ?>" data-doc-category="Uploaded Complaint-Assisted Form" title="Print">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <a href="<?php echo htmlspecialchars($fileUrl); ?>" download="<?php echo htmlspecialchars($primaryDoc['original_name']); ?>" class="btn btn-sm btn-primary" title="Download" style="color:#fff !important;">
                                                <i class="fas fa-download" style="color:#fff;"></i> <span class="d-none d-sm-inline">Download</span>
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <p style="color:#666;margin-bottom:20px;">No uploaded complaint form found.</p>
                        <?php endif; ?>

                        <!-- Valid ID / Credentials -->
                        <?php if (!empty($validIdDocs)): ?>
                            <div class="doc-category-section" style="margin-bottom:24px;">
                                <h4 style="margin:0 0 12px;font-size:14px;font-weight:600;color:#374151;display:flex;align-items:center;gap:8px;">
                                    <i class="fas fa-id-card" style="color:#1b4a9a;"></i> Valid ID / Credentials:
                                </h4>
                                <ul class="doc-list" style="list-style:none;padding:0;margin:0;">
                                    <?php foreach ($validIdDocs as $doc):
                                        $fileExt = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                                        $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        $isPdf = ($fileExt === 'pdf');
                                        $docType = $isImage ? 'image' : ($isPdf ? 'pdf' : 'other');
                                        // Use relative path from file_path column, fallback to old structure for backward compatibility
                                        $fileUrl = !empty($doc['file_path']) ? "/SDO-cts/" . $doc['file_path'] : "/SDO-cts/uploads/complaints/" . $complaint['id'] . "/" . $doc['file_name'];
                                        $iconClass = $isImage ? 'fa-image' : ($isPdf ? 'fa-file-pdf' : 'fa-file');
                                        $iconColor = $isImage ? '#10b981' : ($isPdf ? '#ef4444' : '#6b7280');
                                        ?>
                                        <li class="doc-item" style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:8px;flex-wrap:nowrap;gap:12px;">
                                            <div class="doc-info" style="display:flex;align-items:center;gap:12px;min-width:0;flex:1;overflow:hidden;">
                                                <i class="fas <?php echo $iconClass; ?>" style="font-size:24px;color:<?php echo $iconColor; ?>;flex-shrink:0;"></i>
                                                <div style="min-width:0;flex:1;overflow:hidden;">
                                                    <div style="font-weight:500;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                        <?php echo htmlspecialchars($doc['original_name']); ?>
                                                    </div>
                                                    <div style="font-size:12px;color:#6b7280;">
                                                        <?php echo number_format($doc['file_size'] / 1024, 1); ?> KB
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="doc-actions" style="display:flex;align-items:center;gap:8px;flex-shrink:0;flex-grow:0;">
                                                <button type="button" class="btn btn-sm btn-outline doc-modal-btn" 
                                                        data-url="<?php echo htmlspecialchars($fileUrl); ?>" 
                                                        data-type="<?php echo $docType; ?>" 
                                                        data-name="<?php echo htmlspecialchars($doc['original_name']); ?>" 
                                                        data-doc-category="Valid ID / Credentials"
                                                        title="View in popup">
                                                    <i class="fas fa-expand"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline doc-print-btn" data-url="<?php echo htmlspecialchars($fileUrl); ?>" data-type="<?php echo $docType; ?>" data-doc-category="Valid ID / Credentials" title="Print">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <a href="<?php echo htmlspecialchars($fileUrl); ?>" download="<?php echo htmlspecialchars($doc['original_name']); ?>" class="btn btn-sm btn-primary" title="Download" style="color:#fff !important;">
                                                    <i class="fas fa-download" style="color:#fff;"></i> <span class="d-none d-sm-inline">Download</span>
                                                </a>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Supporting Documents -->
                        <?php if (!empty($supportingDocs)): ?>
                            <div class="doc-category-section" style="margin-bottom:24px;">
                                <h4 style="margin:0 0 12px;font-size:14px;font-weight:600;color:#374151;display:flex;align-items:center;gap:8px;">
                                    <i class="fas fa-paperclip" style="color:#1b4a9a;"></i> Supporting Documents:
                                </h4>
                                <ul class="doc-list" style="list-style:none;padding:0;margin:0;">
                                    <?php foreach ($supportingDocs as $doc):
                                        $fileExt = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                                        $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        $isPdf = ($fileExt === 'pdf');
                                        $docType = $isImage ? 'image' : ($isPdf ? 'pdf' : 'other');
                                        // Use relative path from file_path column, fallback to old structure for backward compatibility
                                        $fileUrl = !empty($doc['file_path']) ? "/SDO-cts/" . $doc['file_path'] : "/SDO-cts/uploads/complaints/" . $complaint['id'] . "/" . $doc['file_name'];
                                        $iconClass = $isImage ? 'fa-image' : ($isPdf ? 'fa-file-pdf' : 'fa-file');
                                        $iconColor = $isImage ? '#10b981' : ($isPdf ? '#ef4444' : '#6b7280');
                                        ?>
                                        <li class="doc-item" style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:8px;flex-wrap:nowrap;gap:12px;">
                                            <div class="doc-info" style="display:flex;align-items:center;gap:12px;min-width:0;flex:1;overflow:hidden;">
                                                <i class="fas <?php echo $iconClass; ?>" style="font-size:24px;color:<?php echo $iconColor; ?>;flex-shrink:0;"></i>
                                                <div style="min-width:0;flex:1;overflow:hidden;">
                                                    <div style="font-weight:500;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                        <?php echo htmlspecialchars($doc['original_name']); ?>
                                                    </div>
                                                    <div style="font-size:12px;color:#6b7280;">
                                                        <?php echo number_format($doc['file_size'] / 1024, 1); ?> KB
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="doc-actions" style="display:flex;align-items:center;gap:8px;flex-shrink:0;flex-grow:0;">
                                                <button type="button" class="btn btn-sm btn-outline doc-modal-btn" 
                                                        data-url="<?php echo htmlspecialchars($fileUrl); ?>" 
                                                        data-type="<?php echo $docType; ?>" 
                                                        data-name="<?php echo htmlspecialchars($doc['original_name']); ?>" 
                                                        data-doc-category="Supporting Documents"
                                                        title="View in popup">
                                                    <i class="fas fa-expand"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline doc-print-btn" data-url="<?php echo htmlspecialchars($fileUrl); ?>" data-type="<?php echo $docType; ?>" data-doc-category="Supporting Documents" title="Print">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <a href="<?php echo htmlspecialchars($fileUrl); ?>" download="<?php echo htmlspecialchars($doc['original_name']); ?>" class="btn btn-sm btn-primary" title="Download" style="color:#fff !important;">
                                                    <i class="fas fa-download" style="color:#fff;"></i> <span class="d-none d-sm-inline">Download</span>
                                                </a>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p>No uploaded documents found for this complaint.</p>
                <?php endif; ?>
        <?php else: ?>
                <!-- STANDARD TYPED FORM MODE: show official template with overlay -->
                <!-- FORM WITH IMAGE BACKGROUND AND TEXT OVERLAY -->
                <div class="form-container">
                    <!-- Background Image (Official Form) -->
                    <img src="/SDO-cts/reference/COMPLAINT-ASSISTED-FORM_1.jpg" 
                         alt="Complaint Assisted Form" 
                         class="form-background">
                
                    <!-- Text Overlay Layer with Positioned Field Boxes -->
                    <div class="form-overlay">
                    
                        <!-- CTS Ticket Number -->
                        <div class="field-box cts-ticket-box">CTS No: <?php echo htmlspecialchars($complaint['reference_number']); ?></div>
                    
                        <!-- Date -->
                        <div class="field-box date-box"><?php echo date('F j, Y', strtotime($complaint['date_petsa'])); ?></div>
                    
                        <!-- Complainant Information -->
                        <div class="field-box complainant-name-box"><?php echo htmlspecialchars($complaint['name_pangalan']); ?></div>
                        <div class="field-box complainant-address-box"><?php echo htmlspecialchars($complaint['address_tirahan']); ?></div>
                        <div class="field-box complainant-contact-box"><?php echo htmlspecialchars($complaint['contact_number']); ?></div>
                        <div class="field-box complainant-email-box"><?php echo htmlspecialchars($complaint['email_address']); ?></div>
                    
                        <!-- Involved Person/Office -->
                        <div class="field-box involved-name-box"><?php echo htmlspecialchars($complaint['involved_full_name']); ?></div>
                        <div class="field-box involved-position-box"><?php echo htmlspecialchars($complaint['involved_position']); ?></div>
                        <div class="field-box involved-address-box"><?php echo htmlspecialchars($complaint['involved_address']); ?></div>
                        <div class="field-box involved-school-box"><?php echo htmlspecialchars(getUnitDisplayName($complaint['involved_school_office_unit'])); ?></div>
                    
                        <!-- Narration (Multi-line, Controlled) -->
                        <div class="field-box narration-box"><?php echo htmlspecialchars($complaint['narration_complaint']); ?></div>
                    
                        <!-- Signature -->
                        <div class="field-box signature-box"><?php echo htmlspecialchars($complaint['signature_data'] ?? $complaint['printed_name_pangalan']); ?></div>
                    
                    </div>
                </div>
                <div class="page-indicator no-print">Page 1 of <?php echo !empty($complaint['narration_complaint_page2']) ? '2' : '1'; ?></div>

                <!-- PAGE 2: ADDITIONAL PAGE FOR NARRATION CONTINUATION (Only if content exists) -->
                <?php if (!empty($complaint['narration_complaint_page2'])): ?>
                    <div class="additional-page">
                        <div class="page-number-label">CTS No: <?php echo htmlspecialchars($complaint['reference_number']); ?> | Page 2</div>
                
                        <div class="additional-page-header">
                            <h2>NARRATION OF COMPLAINT/INQUIRY AND RELIEF</h2>
                            <p>(Ano ang iyong reklamo, tanong, request o suhestiyon? Ano ang gusto mong aksiyon?)</p>
                        </div>
                
                        <div class="additional-page-content"><?php echo htmlspecialchars($complaint['narration_complaint_page2']); ?></div>
                    </div>
                    <div class="page-indicator no-print">Page 2 of 2</div>
                <?php endif; ?>

                <!-- Attached Files (Below Form) -->
                <?php if (!empty($documents)): ?>
                    <?php
                    // Separate documents by category
                    $handwrittenDocs = array_filter($documents, function ($d) {
                        return ($d['category'] ?? '') === 'handwritten_form'; });
                    $validIdDocs = array_filter($documents, function ($d) {
                        return ($d['category'] ?? '') === 'valid_id'; });
                    $supportingDocs = array_filter($documents, function ($d) {
                        $cat = $d['category'] ?? 'supporting';
                        return $cat === 'supporting' || $cat === '';
                    });
                    $docIndex = 0;
                    ?>
                    <div class="attached-notice no-print">
                        <?php if (!empty($handwrittenDocs)): ?>
                            <div style="margin-bottom:16px;">
                                <strong>📝 Uploaded Completed Complaint-Assisted Form:</strong>
                                <ul style="margin-top:8px;list-style:none;padding:0;">
                                    <?php foreach ($handwrittenDocs as $doc): ?>
                                        <?php
                                        // Use relative path from file_path column, fallback to old structure for backward compatibility
                                        $fileUrl = !empty($doc['file_path']) ? "/SDO-cts/" . $doc['file_path'] : "/SDO-cts/uploads/complaints/" . $complaint['id'] . "/" . $doc['file_name'];
                                        $ext = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                        $isPdf = ($ext === 'pdf');
                                        $type = $isImage ? 'image' : ($isPdf ? 'pdf' : 'other');
                                        ?>
                                        <li style="margin-bottom:10px;padding:10px;background:#f8f9fa;border-radius:6px;display:flex;align-items:center;justify-content:space-between;flex-wrap:nowrap;gap:12px;">
                                            <div style="display:flex;align-items:center;gap:8px;min-width:0;flex:1;overflow:hidden;">
                                                <i class="fas <?php echo $isImage ? 'fa-image' : ($isPdf ? 'fa-file-pdf' : 'fa-file'); ?>" style="color:<?php echo $isImage ? '#10b981' : ($isPdf ? '#ef4444' : '#6b7280'); ?>;font-size:1.2rem;flex-shrink:0;"></i>
                                                <div style="min-width:0;flex:1;overflow:hidden;">
                                                    <span style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;"><?php echo htmlspecialchars($doc['original_name']); ?></span>
                                                    <span style="color:#666;font-size:12px;display:block;"><?php echo number_format($doc['file_size'] / 1024, 1); ?> KB</span>
                                                </div>
                                            </div>
                                            <div style="display:flex;gap:6px;flex-shrink:0;flex-grow:0;">
                                                <button type="button" class="btn btn-sm btn-outline doc-modal-btn" data-url="<?php echo htmlspecialchars($fileUrl); ?>" data-type="<?php echo $type; ?>" data-name="<?php echo htmlspecialchars($doc['original_name']); ?>" data-doc-category="Uploaded Completed Complaint-Assisted Form" title="View in popup">
                                                    <i class="fas fa-expand"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline doc-print-btn" data-url="<?php echo htmlspecialchars($fileUrl); ?>" data-type="<?php echo $type; ?>" data-doc-category="Uploaded Completed Complaint-Assisted Form" title="Print">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <a href="<?php echo htmlspecialchars($fileUrl); ?>" download="<?php echo htmlspecialchars($doc['original_name']); ?>" class="btn btn-sm btn-primary" title="Download" style="color:#fff !important;">
                                                    <i class="fas fa-download" style="color:#fff;"></i> <span class="d-none d-sm-inline">Download</span>
                                                </a>
                                            </div>
                                        </li>
                                        <?php $docIndex++; endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($validIdDocs)): ?>
                            <div style="margin-bottom:16px;">
                                <strong>🪪 Valid ID / Credentials:</strong>
                                <ul style="margin-top:8px;list-style:none;padding:0;">
                                    <?php foreach ($validIdDocs as $doc): ?>
                                        <?php
                                        // Use relative path from file_path column, fallback to old structure for backward compatibility
                                        $fileUrl = !empty($doc['file_path']) ? "/SDO-cts/" . $doc['file_path'] : "/SDO-cts/uploads/complaints/" . $complaint['id'] . "/" . $doc['file_name'];
                                        $ext = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                        $isPdf = ($ext === 'pdf');
                                        $type = $isImage ? 'image' : ($isPdf ? 'pdf' : 'other');
                                        ?>
                                        <li style="margin-bottom:10px;padding:10px;background:#f8f9fa;border-radius:6px;display:flex;align-items:center;justify-content:space-between;flex-wrap:nowrap;gap:12px;">
                                            <div style="display:flex;align-items:center;gap:8px;min-width:0;flex:1;overflow:hidden;">
                                                <i class="fas <?php echo $isImage ? 'fa-image' : ($isPdf ? 'fa-file-pdf' : 'fa-file'); ?>" style="color:<?php echo $isImage ? '#10b981' : ($isPdf ? '#ef4444' : '#6b7280'); ?>;font-size:1.2rem;flex-shrink:0;"></i>
                                                <div style="min-width:0;flex:1;overflow:hidden;">
                                                    <span style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;"><?php echo htmlspecialchars($doc['original_name']); ?></span>
                                                    <span style="color:#666;font-size:12px;display:block;"><?php echo number_format($doc['file_size'] / 1024, 1); ?> KB</span>
                                                </div>
                                            </div>
                                            <div style="display:flex;gap:6px;flex-shrink:0;flex-grow:0;">
                                                <button type="button" class="btn btn-sm btn-outline doc-modal-btn" data-url="<?php echo htmlspecialchars($fileUrl); ?>" data-type="<?php echo $type; ?>" data-name="<?php echo htmlspecialchars($doc['original_name']); ?>" data-doc-category="Valid ID / Credentials" title="View in popup">
                                                    <i class="fas fa-expand"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline doc-print-btn" data-url="<?php echo htmlspecialchars($fileUrl); ?>" data-type="<?php echo $type; ?>" data-doc-category="Valid ID / Credentials" title="Print">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <a href="<?php echo htmlspecialchars($fileUrl); ?>" download="<?php echo htmlspecialchars($doc['original_name']); ?>" class="btn btn-sm btn-primary" title="Download" style="color:#fff !important;">
                                                    <i class="fas fa-download" style="color:#fff;"></i> <span class="d-none d-sm-inline">Download</span>
                                                </a>
                                            </div>
                                        </li>
                                        <?php $docIndex++; endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($supportingDocs)): ?>
                            <div style="margin-bottom:16px;">
                                <strong>📎 Supporting Documents:</strong>
                                <ul style="margin-top:8px;list-style:none;padding:0;">
                                    <?php foreach ($supportingDocs as $doc): ?>
                                        <?php
                                        // Use relative path from file_path column, fallback to old structure for backward compatibility
                                        $fileUrl = !empty($doc['file_path']) ? "/SDO-cts/" . $doc['file_path'] : "/SDO-cts/uploads/complaints/" . $complaint['id'] . "/" . $doc['file_name'];
                                        $ext = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                        $isPdf = ($ext === 'pdf');
                                        $type = $isImage ? 'image' : ($isPdf ? 'pdf' : 'other');
                                        ?>
                                        <li style="margin-bottom:10px;padding:10px;background:#f8f9fa;border-radius:6px;display:flex;align-items:center;justify-content:space-between;flex-wrap:nowrap;gap:12px;">
                                            <div style="display:flex;align-items:center;gap:8px;min-width:0;flex:1;overflow:hidden;">
                                                <i class="fas <?php echo $isImage ? 'fa-image' : ($isPdf ? 'fa-file-pdf' : 'fa-file'); ?>" style="color:<?php echo $isImage ? '#10b981' : ($isPdf ? '#ef4444' : '#6b7280'); ?>;font-size:1.2rem;flex-shrink:0;"></i>
                                                <div style="min-width:0;flex:1;overflow:hidden;">
                                                    <span style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;"><?php echo htmlspecialchars($doc['original_name']); ?></span>
                                                    <span style="color:#666;font-size:12px;display:block;"><?php echo number_format($doc['file_size'] / 1024, 1); ?> KB</span>
                                                </div>
                                            </div>
                                            <div style="display:flex;gap:6px;flex-shrink:0;flex-grow:0;">
                                                <button type="button" class="btn btn-sm btn-outline doc-modal-btn" data-url="<?php echo htmlspecialchars($fileUrl); ?>" data-type="<?php echo $type; ?>" data-name="<?php echo htmlspecialchars($doc['original_name']); ?>" data-doc-category="Supporting Documents" title="View in popup">
                                                    <i class="fas fa-expand"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline doc-print-btn" data-url="<?php echo htmlspecialchars($fileUrl); ?>" data-type="<?php echo $type; ?>" data-doc-category="Supporting Documents" title="Print">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <a href="<?php echo htmlspecialchars($fileUrl); ?>" download="<?php echo htmlspecialchars($doc['original_name']); ?>" class="btn btn-sm btn-primary" title="Download" style="color:#fff !important;">
                                                    <i class="fas fa-download" style="color:#fff;"></i> <span class="d-none d-sm-inline">Download</span>
                                                </a>
                                            </div>
                                        </li>
                                        <?php $docIndex++; endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="complaint-sidebar">
        <!-- Actions -->
        <?php if ($auth->hasPermission('complaints.update')): ?>
            <div class="detail-card action-card">
                <div class="detail-card-header">
                    <h3><i class=""></i> Actions</h3>
                </div>
                <div class="detail-card-body">
                    <?php
                    $allowedTransitions = $statusWorkflow[$complaint['status']] ?? [];
                    // Separate progress update (self-loop) from actual status changes
                    $statusChangeTransitions = array_filter($allowedTransitions, function($s) use ($complaint) {
                        return $s !== $complaint['status'];
                    });
                    $canAddProgress = in_array($complaint['status'], $allowedTransitions);
                    ?>
                
                    <?php if ($complaint['status'] === 'pending' && $auth->hasPermission('complaints.accept')): ?>
                        <button type="button" class="btn btn-success btn-block" onclick="openActionModal('accept')">
                            <i class=""></i> Accept Complaint
                        </button>
                        <button type="button" class="btn btn-outline btn-block btn-danger-outline" onclick="openActionModal('return')">
                            <i class=""></i> Return Complaint
                        </button>
                    <?php endif; ?>

                    <?php if ($canAddProgress): ?>
                        <button type="button" class="btn btn-block" style="background:#8b5cf6;color:#fff;border:none;" onclick="openProgressModal()">
                            <i class="fas fa-clipboard-list"></i> Add Progress Update
                        </button>
                    <?php endif; ?>
                
                    <?php if (!empty($statusChangeTransitions) && $complaint['status'] !== 'pending'): ?>
                        <button type="button" class="btn btn-primary btn-block" onclick="openStatusModal()">
                            <i class="fas fa-exchange-alt"></i> Update Status
                        </button>
                    <?php endif; ?>
                
                    <?php if (empty($allowedTransitions) && $complaint['status'] !== 'pending'): ?>
                        <p class="action-note">No further actions available for this status.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Progress Tracker -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3><i class="fas fa-tasks"></i> Progress Tracker</h3>
            </div>
            <div class="detail-card-body">
                <?php
                $statusOrder = ['pending', 'accepted', 'in_progress', 'closed'];
                $currentIndex = array_search($complaint['status'], $statusOrder);
                if ($complaint['status'] === 'returned') {
                    $currentIndex = -1;
                }
                ?>
                <div class="progress-tracker-vertical">
                    <?php foreach ($statusOrder as $index => $step): ?>
                        <?php
                        $stepClass = '';
                        if ($complaint['status'] === 'returned') {
                            $stepClass = 'returned';
                        } elseif ($index < $currentIndex) {
                            $stepClass = 'completed';
                        } elseif ($index === $currentIndex) {
                            $stepClass = 'current';
                        }
                        ?>
                        <div class="progress-step <?php echo $stepClass; ?>">
                            <div class="step-marker">
                                <?php if ($index < $currentIndex): ?>
                                        <i class="fas fa-check"></i>
                                <?php else: ?>
                                        <?php echo $statusConfig[$step]['icon']; ?>
                                <?php endif; ?>
                            </div>
                            <div class="step-content">
                                <span class="step-label"><?php echo $statusConfig[$step]['label']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if ($complaint['status'] === 'returned'): ?>
                        <div class="progress-step returned current">
                            <div class="step-marker"><i class="fas fa-undo"></i></div>
                            <div class="step-content">
                                <span class="step-label">Returned</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Status History -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3><i class="fas fa-history"></i> Activity Log</h3>
            </div>
            <div class="detail-card-body">
                <div class="timeline">
                    <?php
                    // Track consecutive in_progress entries to label them as progress updates
                    $prevStatus = null;
                    $progressCount = 0;
                    // Reverse iterate to count progress updates chronologically
                    $historyWithMeta = [];
                    $tempCount = 0;
                    $reversedHistory = array_reverse($history);
                    foreach ($reversedHistory as $entry) {
                        if ($entry['status'] === 'in_progress') {
                            $tempCount++;
                            $entry['_is_progress_update'] = ($tempCount > 1);
                            $entry['_progress_number'] = $tempCount;
                        } else {
                            $tempCount = 0;
                            $entry['_is_progress_update'] = false;
                            $entry['_progress_number'] = 0;
                        }
                        $historyWithMeta[] = $entry;
                    }
                    $historyWithMeta = array_reverse($historyWithMeta);
                    ?>
                    <?php foreach ($historyWithMeta as $entry): ?>
                        <?php $isProgressNote = $entry['_is_progress_update']; ?>
                        <div class="timeline-item <?php echo $isProgressNote ? 'timeline-progress-update' : ''; ?>">
                            <div class="timeline-marker" <?php if ($isProgressNote): ?>style="background:#8b5cf6;border-color:#8b5cf6;"<?php endif; ?>></div>
                            <div class="timeline-content">
                                <div class="timeline-status">
                                    <?php if ($isProgressNote): ?>
                                        <span style="color:#8b5cf6;"><i class="fas fa-clipboard-list"></i> Progress Update #<?php echo $entry['_progress_number'] - 1; ?></span>
                                    <?php else: ?>
                                        <?php echo $statusConfig[$entry['status']]['icon'] . ' ' . $statusConfig[$entry['status']]['label']; ?>
                                    <?php endif; ?>
                                </div>
                                <?php if ($entry['notes']): ?>
                                    <div class="timeline-notes" <?php if ($isProgressNote): ?>style="background:#f5f3ff;border-left:3px solid #8b5cf6;padding:8px 12px;border-radius:0 6px 6px 0;margin-top:6px;"<?php endif; ?>><?php echo htmlspecialchars($entry['notes']); ?></div>
                                <?php endif; ?>
                                <div class="timeline-meta">
                                    <span><?php echo htmlspecialchars($entry['admin_name'] ?? $entry['updated_by']); ?></span>
                                    <span><?php echo date('M j, Y g:i A', strtotime($entry['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Action Modals -->

<!-- Accept/Return Modal -->
<div class="modal-overlay" id="actionModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="actionModalTitle">Action</h3>
            <button type="button" class="modal-close" onclick="closeModal('actionModal')">&times;</button>
        </div>
        <form method="POST" action="/SDO-cts/admin/api/complaint-action.php" id="actionForm">
            <div class="modal-body">
                <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                <input type="hidden" name="action" id="actionType">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                
                <div class="form-group">
                    <label class="form-label" id="actionNotesLabel">Notes</label>
                    <textarea name="notes" class="form-control" rows="4" id="actionNotes" placeholder="Add notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('actionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="actionSubmitBtn">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal-overlay" id="statusModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-exchange-alt"></i> Update Status</h3>
            <button type="button" class="modal-close" onclick="closeModal('statusModal')">&times;</button>
        </div>
        <form method="POST" action="/SDO-cts/admin/api/update-status.php">
            <div class="modal-body">
                <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                
                <div class="form-group">
                    <label class="form-label">New Status</label>
                    <select name="status" class="form-control" required>
                        <?php
                        // Only show actual status changes (not self-loop) in this modal
                        $statusChangeOnly = array_filter($allowedTransitions, function($s) use ($complaint) {
                            return $s !== $complaint['status'];
                        });
                        foreach ($statusChangeOnly as $status): ?>
                            <option value="<?php echo $status; ?>">
                                <?php echo $statusConfig[$status]['label']; ?>
                            </option>
                        <?php endforeach; ?>
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

<!-- Progress Update Modal -->
<div class="modal-overlay" id="progressModal">
    <div class="modal">
        <div class="modal-header" style="border-bottom:2px solid #8b5cf6;">
            <h3 style="color:#8b5cf6;"><i class="fas fa-clipboard-list"></i> Add Progress Update</h3>
            <button type="button" class="modal-close" onclick="closeModal('progressModal')">&times;</button>
        </div>
        <form method="POST" action="/SDO-cts/admin/api/update-status.php">
            <div class="modal-body">
                <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                <input type="hidden" name="status" value="in_progress">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                
                <div style="background:#f5f3ff;border:1px solid #e9e5ff;border-radius:8px;padding:14px 16px;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                        <i class="fas fa-info-circle" style="color:#8b5cf6;"></i>
                        <span style="font-weight:600;color:#6d28d9;font-size:13px;">Progress Note</span>
                    </div>
                    <p style="margin:0;font-size:13px;color:#5b21b6;line-height:1.5;">The complaint will stay <strong>In Progress</strong>. Your note will be recorded in the activity log as a progress update.</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Progress Note <span style="color:#ef4444;">*</span></label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Describe the current progress, actions taken, findings, or next steps..." required></textarea>
                    <small style="color:#6b7280;margin-top:4px;display:block;">This note will be visible in the activity log and complaint tracking.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('progressModal')">Cancel</button>
                <button type="submit" class="btn" style="background:#8b5cf6;color:#fff;border:none;"><i class="fas fa-plus"></i> Add Progress Update</button>
            </div>
        </form>
    </div>
</div>

<script>
// Global flags for Save Document behavior
const IS_UPLOADED_FORM   = <?php echo $isUploadedForm ? 'true' : 'false'; ?>;
const PRIMARY_DOC_URL    = <?php echo json_encode($primaryUrl); ?>;
const PRIMARY_DOC_EXT    = <?php echo json_encode($primaryExt); ?>;
const PRIMARY_DOC_NAME   = <?php echo json_encode($primaryOriginalName); ?>;
function openActionModal(action) {
    const modal = document.getElementById('actionModal');
    const title = document.getElementById('actionModalTitle');
    const notesLabel = document.getElementById('actionNotesLabel');
    const notesInput = document.getElementById('actionNotes');
    const actionInput = document.getElementById('actionType');
    const submitBtn = document.getElementById('actionSubmitBtn');
    
    actionInput.value = action;
    
    if (action === 'accept') {
        title.innerHTML = '<i class="fas fa-check"></i> Accept Complaint';
        notesLabel.textContent = 'Notes (Optional)';
        notesInput.placeholder = 'Add any notes about accepting this complaint...';
        notesInput.required = false;
        submitBtn.textContent = 'Accept Complaint';
        submitBtn.className = 'btn btn-success';
    } else if (action === 'return') {
        title.innerHTML = '<i class="fas fa-undo"></i> Return Complaint';
        notesLabel.textContent = 'Reason for Return *';
        notesInput.placeholder = 'Please provide the reason for returning this complaint...';
        notesInput.required = true;
        submitBtn.textContent = 'Return Complaint';
        submitBtn.className = 'btn btn-danger';
    }
    
    modal.classList.add('active');
}

function openStatusModal() {
    document.getElementById('statusModal').classList.add('active');
}

function openProgressModal() {
    document.getElementById('progressModal').classList.add('active');
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

// Save as PDF function
function saveAsPDF(button) {
    // Get complainant name and sanitize it for filename
    const complainantName = '<?php echo addslashes($complaint['name_pangalan']); ?>';
    const refNumber = '<?php echo htmlspecialchars($complaint['reference_number']); ?>';
    
    // Sanitize filename: remove special characters, replace spaces with underscores
    const sanitizedName = complainantName
        .replace(/[^a-zA-Z0-9\s]/g, '') // Remove special characters
        .replace(/\s+/g, '_') // Replace spaces with underscores
        .substring(0, 50); // Limit length
    
    // Create filename: ComplainantName_ReferenceNumber.pdf
    const filename = (sanitizedName || 'Complaint') + '_' + refNumber + '.pdf';

    // If this is an uploaded-form complaint, always download the original
    // uploaded file (PDF/image/etc.) instead of screenshotting the view.
    if (IS_UPLOADED_FORM && PRIMARY_DOC_URL) {
        const downloadName = PRIMARY_DOC_NAME || (filename + (PRIMARY_DOC_EXT ? '.' + PRIMARY_DOC_EXT : ''));
        // Fetch as blob to force download (avoids browser opening a new tab for PDF)
        fetch(PRIMARY_DOC_URL)
            .then(res => res.blob())
            .then(blob => {
                const blobUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = downloadName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(blobUrl);
            })
            .catch(() => {
                // Fallback to direct link if blob download fails
                const link = document.createElement('a');
                link.href = PRIMARY_DOC_URL;
                link.download = downloadName;
                link.target = '_self';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        return;
    }
    
    // Get the element to convert
    // - For uploaded image/other docs: capture only the uploaded document container
    // - For standard typed complaints: capture the entire complaint-main section
    const element = (IS_UPLOADED_FORM && document.getElementById('uploadedDocContainer'))
        ? document.getElementById('uploadedDocContainer')
        : document.querySelector('.complaint-main');
    
    // Show loading indicator
    const originalBtn = button || document.querySelector('button[onclick*="saveAsPDF"]');
    const originalText = originalBtn.innerHTML;
    originalBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
    originalBtn.disabled = true;
    
    // Hide page indicators and attached notice before PDF generation (typed complaints only)
    const pageIndicators = element.querySelectorAll ? element.querySelectorAll('.page-indicator') : [];
    const attachedNotice = element.querySelector ? element.querySelector('.attached-notice') : null;
    if (pageIndicators.forEach) {
        pageIndicators.forEach(indicator => indicator.style.display = 'none');
    }
    if (attachedNotice) attachedNotice.style.display = 'none';
    
    // Wait for images to load, then generate PDF
    const images = element.querySelectorAll('img');
    let imagesLoaded = 0;
    const totalImages = images.length;
    
    const generatePDF = () => {
        // Use the original visible elements directly
        // html2pdf works best with visible, rendered elements
        const formContainer = element.querySelector('.form-container');
        const additionalPage = element.querySelector('.additional-page');
        
        if (!formContainer) {
            alert('No content to generate PDF');
            originalBtn.innerHTML = originalText;
            originalBtn.disabled = false;
            return;
        }
        
        // Temporarily hide sidebar and other non-essential elements
        const sidebar = document.querySelector('.complaint-sidebar');
        const adminHeader = document.querySelector('.admin-view-header');
        const originalSidebarDisplay = sidebar ? sidebar.style.display : '';
        const originalHeaderDisplay = adminHeader ? adminHeader.style.display : '';
        
        if (sidebar) sidebar.style.display = 'none';
        if (adminHeader) adminHeader.style.display = 'none';
        
        // Create a container that wraps just the content we want
        const pdfContent = document.createElement('div');
        pdfContent.style.width = '850px';
        pdfContent.style.margin = '0 auto';
        pdfContent.style.backgroundColor = '#fff';
        
        // Use the original elements - they're already visible and rendered
        // We'll capture the complaint-main div which contains both pages
        const contentToCapture = element; // Use the entire complaint-main element
        
        // Configure html2pdf options
        const opt = {
            margin: [0, 0, 0, 0],
            filename: filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2,
                useCORS: true,
                logging: false,
                letterRendering: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                windowWidth: window.innerWidth,
                windowHeight: window.innerHeight
            },
            jsPDF: { 
                unit: 'mm', 
                format: 'a4', 
                orientation: 'portrait',
                compress: true
            },
          
        };
        
        // Generate and download PDF from the visible element
        html2pdf().set(opt).from(contentToCapture).save().then(() => {
            // Restore hidden elements
            if (sidebar) sidebar.style.display = originalSidebarDisplay;
            if (adminHeader) adminHeader.style.display = originalHeaderDisplay;
            
            // Restore page indicators
            pageIndicators.forEach(indicator => indicator.style.display = '');
            if (attachedNotice) attachedNotice.style.display = '';
            
            // Restore button
            originalBtn.innerHTML = originalText;
            originalBtn.disabled = false;
        }).catch((error) => {
            console.error('PDF generation error:', error);
            
            // Restore hidden elements on error
            if (sidebar) sidebar.style.display = originalSidebarDisplay;
            if (adminHeader) adminHeader.style.display = originalHeaderDisplay;
            
            // Restore page indicators
            pageIndicators.forEach(indicator => indicator.style.display = '');
            if (attachedNotice) attachedNotice.style.display = '';
            
            alert('Error generating PDF. Please try using the Print button instead.');
            originalBtn.innerHTML = originalText;
            originalBtn.disabled = false;
        });
    };
    
    // Wait for all images to load
    if (totalImages === 0) {
        setTimeout(generatePDF, 100); // Small delay to ensure DOM is ready
    } else {
        images.forEach(img => {
            if (img.complete) {
                imagesLoaded++;
                if (imagesLoaded === totalImages) {
                    setTimeout(generatePDF, 100);
                }
            } else {
                img.onload = () => {
                    imagesLoaded++;
                    if (imagesLoaded === totalImages) {
                        setTimeout(generatePDF, 100);
                    }
                };
                img.onerror = () => {
                    imagesLoaded++;
                    if (imagesLoaded === totalImages) {
                        setTimeout(generatePDF, 100);
                    }
                };
            }
        });
    }
}

// Print document (uploaded-form: print the original file; typed: print the page)
function printDocument() {
    console.log('printDocument called');
    console.log('IS_UPLOADED_FORM:', IS_UPLOADED_FORM);
    
    if (IS_UPLOADED_FORM && PRIMARY_DOC_URL) {
        // For uploaded forms, open the document in a new window for printing
        if (PRIMARY_DOC_EXT === 'pdf') {
            window.open(PRIMARY_DOC_URL, '_blank');
            alert('The PDF has been opened in a new tab. Please use Ctrl+P (or Cmd+P on Mac) to print it.');
            return;
        }

        // For images: create a printable page with logos
        var printWin = window.open('', '_blank');
        if (printWin) {
            var sdoLogoPath = '/SDO-cts/assets/img/sdo-logo.jpg';
            var bagongPilipinasLogoPath = '/SDO-cts/assets/img/bagongpilpinas-logo.png';
            var refNumber = '<?php echo htmlspecialchars($complaint['reference_number'] ?? ''); ?>';
            
            printWin.document.write('<!DOCTYPE html>');
            printWin.document.write('<html>');
            printWin.document.write('<head>');
            printWin.document.write('<title>Uploaded Complaint-Assisted Form</title>');
            printWin.document.write('<style>');
            printWin.document.write('* { margin: 0; padding: 0; box-sizing: border-box; }');
            printWin.document.write('body { background: #fff; }');
            printWin.document.write('.print-header { display: flex; justify-content: space-between; align-items: center; padding: 8px 15px; background: #fff; border-bottom: 2px solid #000; }');
            printWin.document.write('.print-header .sdo-logo { height: 50px; width: 50px; border-radius: 50%; object-fit: cover; }');
            printWin.document.write('.print-header .doc-type-label { flex: 1; text-align: center; }');
            printWin.document.write('.print-header .doc-type-label h3 { margin: 0; font-family: "Times New Roman", Times, serif; font-size: 18px; font-weight: bold; color: #000; text-transform: uppercase; }');
            printWin.document.write('.print-header .doc-type-label p { margin: 2px 0 0 0; font-family: "Times New Roman", Times, serif; font-size: 11px; color: #000; }');
            printWin.document.write('.print-header .bp-logo { height: 50px; width: auto; object-fit: contain; }');
            printWin.document.write('.print-content { display: flex; justify-content: center; align-items: flex-start; padding: 10px; }');
            printWin.document.write('.print-content img { max-width: 100%; height: auto; object-fit: contain; }');
            printWin.document.write('@media print { @page { margin: 5mm; } body { margin: 0; padding: 0; } .print-header { page-break-after: avoid; page-break-inside: avoid; } .print-content { min-height: auto; padding: 5px; } .print-content img { max-width: 100%; max-height: 90vh; height: auto; } }');
            printWin.document.write('</style>');
            printWin.document.write('</head>');
            printWin.document.write('<body>');
            // Add header with SDO logo (left, circle), document type (center), Bagong Pilipinas (right)
            printWin.document.write('<div class="print-header">');
            printWin.document.write('<img src="' + sdoLogoPath + '" alt="SDO Logo" class="sdo-logo" />');
            printWin.document.write('<div class="doc-type-label">');
            printWin.document.write('<h3>Uploaded Complaint-Assisted Form</h3>');
            printWin.document.write('<p>' + (refNumber || '') + '</p>');
            printWin.document.write('</div>');
            printWin.document.write('<img src="' + bagongPilipinasLogoPath + '" alt="Bagong Pilipinas" class="bp-logo" />');
            printWin.document.write('</div>');
            printWin.document.write('<div class="print-content">');
            printWin.document.write('<img src="' + PRIMARY_DOC_URL.replace(/"/g, '&quot;') + '" onload="setTimeout(function(){window.print();}, 500);">');
            printWin.document.write('</div>');
            printWin.document.write('</body>');
            printWin.document.write('</html>');
            printWin.document.close();
        } else {
            alert('Pop-up blocked. Please allow pop-ups and try again.');
        }
        return;
    }

    // For typed complaints: create a clean print window with just the form content
    var formContainers = document.querySelectorAll('.complaint-main .form-container');
    var additionalPage = document.querySelector('.complaint-main .additional-page');
    
    if (formContainers.length === 0) {
        alert('No printable form found.');
        return;
    }
    
    // Clone the content for printing
    var printContent = '';
    formContainers.forEach(function(container) {
        printContent += container.outerHTML;
    });
    if (additionalPage) {
        printContent += additionalPage.outerHTML;
    }
    
    // Get the styles from the current page
    var styles = '';
    document.querySelectorAll('style').forEach(function(style) {
        styles += style.outerHTML;
    });
    
    // Get complainant name and CTS number for PDF filename
    var complainantName = '<?php echo addslashes($complaint['name_pangalan'] ?? 'Complaint'); ?>';
    var refNumber = '<?php echo htmlspecialchars($complaint['reference_number'] ?? ''); ?>';
    
    // Sanitize filename: remove special characters, replace spaces with underscores
    var sanitizedName = complainantName
        .replace(/[^a-zA-Z0-9\s]/g, '') // Remove special characters
        .replace(/\s+/g, '_') // Replace spaces with underscores
        .substring(0, 50); // Limit length
    
    // Create filename: ComplainantName_CTSNumber
    var pdfFilename = (sanitizedName || 'Complaint') + (refNumber ? '_' + refNumber : '');
    
    // Logo paths
    var sdoLogoPath = '/SDO-cts/assets/img/sdo-logo.jpg';
    var bagongPilipinasLogoPath = '/SDO-cts/assets/img/bagongpilpinas-logo.png';
    
    // Create print window
    var printWin = window.open('', '_blank', 'width=900,height=700');
    if (printWin) {
        printWin.document.write('<!DOCTYPE html>');
        printWin.document.write('<html><head><title>' + pdfFilename + '</title>');
        printWin.document.write(styles);
        printWin.document.write('<style>');
        printWin.document.write('* { box-sizing: border-box; }');
        printWin.document.write('body { margin: 0; padding: 10px; background: #fff; }');
        printWin.document.write('.print-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; padding: 6px 12px; background: #fff; border-bottom: 2px solid #000; }');
        printWin.document.write('.print-header .sdo-logo { height: 40px; width: 40px; border-radius: 50%; object-fit: cover; }');
        printWin.document.write('.print-header .doc-type-label { flex: 1; text-align: center; }');
        printWin.document.write('.print-header .doc-type-label h3 { margin: 0; font-family: "Times New Roman", Times, serif; font-size: 16px; font-weight: bold; color: #000; text-transform: uppercase; }');
        printWin.document.write('.print-header .doc-type-label p { margin: 1px 0 0 0; font-family: "Times New Roman", Times, serif; font-size: 10px; color: #000; }');
        printWin.document.write('.print-header .bp-logo { height: 40px; width: auto; object-fit: contain; }');
        printWin.document.write('.form-container { margin: 0 auto 8px; width: 720px; max-width: 720px; page-break-inside: avoid; page-break-after: avoid; break-inside: avoid; break-after: avoid; }');
        printWin.document.write('.additional-page { margin: 10px auto; width: 850px; max-width: 100%; padding: 30px 40px; position: relative; background: #fff; page-break-before: always; }');
        printWin.document.write('.additional-page-header { border: 1px solid #000; border-left: 3px solid #000; border-right: 3px solid #000; border-bottom: none; padding: 10px; background: #fff; }');
        printWin.document.write('.additional-page-content { border: 1px solid #000; border-left: 3px solid #000; border-right: 3px solid #000; border-bottom: 3px solid #000; padding: 5px 10px; background: repeating-linear-gradient(transparent, transparent 27px, #000 27px, #000 28px); }');
        printWin.document.write('@media print { @page { margin: 5mm; } body { padding: 0; margin: 0; } .print-header { page-break-after: avoid; page-break-inside: avoid; margin-bottom: 5px; } .form-container { page-break-inside: avoid !important; page-break-after: avoid !important; break-inside: avoid !important; break-after: avoid !important; box-shadow: none !important; } .additional-page { margin: 0 auto !important; page-break-before: always !important; box-shadow: none !important; } }');
        printWin.document.write('</style>');
        printWin.document.write('</head><body>');
        // Add header with SDO logo (left, circle), document type (center), Bagong Pilipinas (right)
        printWin.document.write('<div class="print-header">');
        printWin.document.write('<img src="' + sdoLogoPath + '" alt="SDO Logo" class="sdo-logo" />');
        printWin.document.write('<div class="doc-type-label">');
        printWin.document.write('<h3>Complaint Form</h3>');
        printWin.document.write('<p>' + (refNumber || '') + '</p>');
        printWin.document.write('</div>');
        printWin.document.write('<img src="' + bagongPilipinasLogoPath + '" alt="Bagong Pilipinas" class="bp-logo" />');
        printWin.document.write('</div>');
        printWin.document.write(printContent);
        printWin.document.write('</body></html>');
        printWin.document.close();
        
        // Wait for content to load then print
        printWin.onload = function() {
            setTimeout(function() {
                printWin.focus();
                printWin.print();
            }, 500);
        };
        
        // Fallback if onload doesn't fire (for some browsers)
        setTimeout(function() {
            printWin.focus();
            printWin.print();
        }, 1000);
    } else {
        alert('Pop-up blocked. Please allow pop-ups for this site to print.');
    }
}

// Document Modal Viewer for all document sections
document.addEventListener('DOMContentLoaded', function () {
    // --- Document Modal Viewer ---
    const docModal = document.getElementById('docViewerModal');
    const docModalTitle = document.getElementById('docModalTitle');
    const docModalContent = document.getElementById('docModalContent');
    const docModalClose = document.getElementById('docModalClose');
    const docModalOverlay = document.querySelector('.doc-modal-overlay');
    const modalBtns = document.querySelectorAll('.doc-modal-btn');
    const printBtns = document.querySelectorAll('.doc-print-btn');
    const modalZoomToolbar = document.getElementById('modalZoomToolbar');
    const modalZoomLabel = document.getElementById('modalZoomLabel');
    const modalDownloadBtn = document.getElementById('modalDownloadBtn');
    const modalPrintBtn = document.getElementById('modalPrintBtn');

    // Print document function (for individual documents)
    function printDocumentFile(url, type, docCategory) {
        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            alert('Pop-up blocked. Please allow pop-ups and try again.');
            return;
        }
        
        // Logo paths
        const sdoLogoPath = '/SDO-cts/assets/img/sdo-logo.jpg';
        const bagongPilipinasLogoPath = '/SDO-cts/assets/img/bagongpilpinas-logo.png';
        
        // Document type label
        const docTypeLabel = docCategory || 'Document';
        
        if (type === 'image') {
            printWindow.document.write('<!DOCTYPE html>');
            printWindow.document.write('<html>');
            printWindow.document.write('<head>');
            printWindow.document.write('<title>Print ' + docTypeLabel + '</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('* { margin: 0; padding: 0; box-sizing: border-box; }');
            printWindow.document.write('body { background: #fff; }');
            printWindow.document.write('.print-header { display: flex; justify-content: space-between; align-items: center; padding: 8px 15px; background: #fff; border-bottom: 2px solid #000; }');
            printWindow.document.write('.print-header .sdo-logo { height: 50px; width: 50px; border-radius: 50%; object-fit: cover; }');
            printWindow.document.write('.print-header .doc-type-label { flex: 1; text-align: center; }');
            printWindow.document.write('.print-header .doc-type-label h3 { margin: 0; font-family: "Times New Roman", Times, serif; font-size: 18px; font-weight: bold; color: #000; text-transform: uppercase; }');
            printWindow.document.write('.print-header .bp-logo { height: 50px; width: auto; object-fit: contain; }');
            printWindow.document.write('.print-content { display: flex; justify-content: center; align-items: flex-start; padding: 10px; }');
            printWindow.document.write('.print-content img { max-width: 100%; height: auto; object-fit: contain; }');
            printWindow.document.write('@media print { @page { margin: 5mm; } body { margin: 0; padding: 0; } .print-header { page-break-after: avoid; page-break-inside: avoid; } .print-content { min-height: auto; padding: 5px; } .print-content img { max-width: 100%; max-height: 90vh; height: auto; } }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head>');
            printWindow.document.write('<body>');
            // Add header with SDO logo (left, circle), document type (center), Bagong Pilipinas (right)
            printWindow.document.write('<div class="print-header">');
            printWindow.document.write('<img src="' + sdoLogoPath + '" alt="SDO Logo" class="sdo-logo" />');
            printWindow.document.write('<div class="doc-type-label">');
            printWindow.document.write('<h3>' + docTypeLabel + '</h3>');
            printWindow.document.write('</div>');
            printWindow.document.write('<img src="' + bagongPilipinasLogoPath + '" alt="Bagong Pilipinas" class="bp-logo" />');
            printWindow.document.write('</div>');
            printWindow.document.write('<div class="print-content">');
            printWindow.document.write('<img src="' + url.replace(/"/g, '&quot;') + '" onload="setTimeout(function(){window.print();}, 500);" />');
            printWindow.document.write('</div>');
            printWindow.document.write('</body>');
            printWindow.document.write('</html>');
            printWindow.document.close();
        } else if (type === 'pdf') {
            // For PDFs, we can't easily add a header, so just open it
            printWindow.location.href = url;
            printWindow.onload = function() {
                setTimeout(function() {
                    printWindow.print();
                }, 500);
            };
        } else {
            printWindow.location.href = url;
        }
    }

    // Print buttons in document list
    printBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            const type = this.getAttribute('data-type');
            const docCategory = this.getAttribute('data-doc-category') || 'Document';
            printDocumentFile(url, type, docCategory);
        });
    });

    if (docModal && modalBtns.length) {
        let modalZoom = 1;
        const MIN_ZOOM = 0.25;
        const MAX_ZOOM = 4;
        const ZOOM_STEP = 0.25;
        let currentDocUrl = '';
        let currentDocName = '';
        let currentDocType = '';
        let currentDocCategory = '';

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

        function openDocModal(url, type, name, category) {
            currentDocUrl = url;
            currentDocName = name;
            currentDocType = type;
            currentDocCategory = category || 'Document';
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
            
            // Update action buttons
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

        // Open modal on button click
        modalBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                const type = this.getAttribute('data-type');
                const name = this.getAttribute('data-name');
                const category = this.getAttribute('data-doc-category') || 'Document';
                openDocModal(url, type, name, category);
            });
        });

        // Print from modal
        if (modalPrintBtn) {
            modalPrintBtn.addEventListener('click', function() {
                if (currentDocUrl) {
                    printDocumentFile(currentDocUrl, currentDocType, currentDocCategory);
                }
            });
        }

        // Close modal
        if (docModalClose) {
            docModalClose.addEventListener('click', closeDocModal);
        }
        if (docModalOverlay) {
            docModalOverlay.addEventListener('click', closeDocModal);
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && docModal.classList.contains('active')) {
                closeDocModal();
            }
        });

        // Zoom controls
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
    }
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
                <button type="button" id="modalPrintBtn" class="btn btn-sm btn-outline" title="Print">
                    <i class="fas fa-print"></i>
                </button>
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

<?php include __DIR__ . '/includes/footer.php'; ?>

