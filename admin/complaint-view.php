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
    $primaryUrl = "/SDO-cts/uploads/complaints/" . $complaint['id'] . "/" . $primaryDoc['file_name'];
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
$hasCoreFieldsEmpty = 
    empty(trim($complaint['name_pangalan'] ?? '')) &&
    empty(trim($complaint['address_tirahan'] ?? '')) &&
    empty(trim($complaint['contact_number'] ?? '')) &&
    empty(trim($complaint['email_address'] ?? '')) &&
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
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        page-break-after: always;
        page-break-inside: avoid;
        break-after: page;
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
        background: linear-gradient(135deg, #1a5a96, #0d3d6e);
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
        color: #1a5a96;
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
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 30px 40px;
        min-height: 1100px;
        page-break-before: always;
        page-break-inside: avoid;
        break-before: page;
        break-inside: avoid;
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
        size: auto;
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
        }
        
        /* Form container styling for print */
        .form-container {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            box-shadow: none !important;
            page-break-after: always;
        }
        
        .form-background {
            width: 100% !important;
        }
        
        /* Additional page print styling */
        .additional-page {
            box-shadow: none !important;
            margin: 0 !important;
            page-break-before: always;
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
</style>

<div class="admin-view-header no-print">
    <a href="/SDO-cts/admin/complaints.php" class="back-link">← Back to Complaints</a>
    <div class="header-actions">
        <span class="reference-badge"><?php echo htmlspecialchars($complaint['reference_number']); ?></span>
        <span class="status-badge status-<?php echo $complaint['status']; ?> large">
            <?php echo $statusConfig[$complaint['status']]['icon'] . ' ' . $statusConfig[$complaint['status']]['label']; ?>
        </span>
                    <button type="button" class="btn btn-outline" onclick="printDocument()">
                        <i class="fas fa-print"></i> Print
        </button>
        <button type="button" class="btn btn-primary" onclick="saveAsPDF(this)">
            <i class="fas fa-file-download"></i> Save Document
        </button>
    </div>
</div>

<div class="complaint-detail-grid">
    <!-- Main Content -->
    <div class="complaint-main">
        <?php if ($isUploadedForm): ?>
            <!-- UPLOADED FORM MODE: show uploaded document(s) instead of blank template -->
            <?php if (!empty($documents)): ?>
            <?php
                // Use the first document as the primary uploaded complaint form for inline viewing
                $primaryDoc = $documents[0];
                $primaryUrl = "/SDO-cts/uploads/complaints/" . $complaint['id'] . "/" . $primaryDoc['file_name'];
                $primaryExt = strtolower(pathinfo($primaryDoc['file_name'], PATHINFO_EXTENSION));
                $primaryIsImage = in_array($primaryExt, ['jpg','jpeg','png']);
                $primaryIsPdf = ($primaryExt === 'pdf');
            ?>
            <div class="form-container" style="box-shadow:none;padding:20px 0;">
                <div style="padding:0 20px 20px;">
                    <h3 style="margin-bottom:10px;">Uploaded Complaint-Assisted Form</h3>
                    <p style="margin:0 0 10px;color:#555;">
                        This complaint was filed via an uploaded completed form. You can zoom and scroll the full document below.
                    </p>

                    <!-- Zoom toolbar for primary uploaded form -->
                    <div id="uploadedZoomToolbar" style="margin-bottom:8px;display:flex;align-items:center;gap:8px;font-size:12px;color:#444;">
                        <span>Zoom:</span>
                        <button type="button" class="btn btn-sm btn-outline" data-zoom="out">−</button>
                        <button type="button" class="btn btn-sm btn-outline" data-zoom="in">+</button>
                        <button type="button" class="btn btn-sm btn-secondary" data-zoom="reset">Reset</button>
                        <span id="uploadedZoomLabel" style="margin-left:4px;">100%</span>
                    </div>

                    <div id="uploadedDocContainer"
                         style="width:100%;height:90vh;border:1px solid #ddd;border-radius:6px;background:#fafafa;overflow:auto;display:flex;align-items:center;justify-content:center;padding:10px;box-sizing:border-box;">
                        <div class="uploaded-preview-inner" style="transform-origin:top left; width:100%; height:100%; max-width:100%; max-height:100%; display:flex; align-items:center; justify-content:center;">
                            <?php if ($primaryIsImage): ?>
                                <img src="<?php echo htmlspecialchars($primaryUrl); ?>"
                                     alt="<?php echo htmlspecialchars($primaryDoc['original_name']); ?>"
                                     style="max-width:100%;max-height:100%;width:auto;height:auto;display:block;margin:0 auto;object-fit:contain;">
                            <?php elseif ($primaryIsPdf): ?>
                                <embed src="<?php echo htmlspecialchars($primaryUrl); ?>" type="application/pdf"
                                       style="width:100%;height:100%;border:none;" />
                            <?php else: ?>
                                <div style="text-align:center;padding:20px;">
                                    <p style="margin-bottom:10px;">Preview not available for this file type.</p>
                                    <a href="<?php echo htmlspecialchars($primaryUrl); ?>" target="_blank" class="btn btn-outline btn-sm">
                                        Open / Download
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (count($documents) > 1): ?>
                    <p style="margin-top:10px;font-size:12px;color:#555;">
                        Other attached documents:
                    </p>
                    <ul style="margin-top:4px;margin-left:18px;">
                        <?php foreach ($documents as $index => $doc): ?>
                            <?php if ($index === 0) continue; ?>
                            <?php $url = "/SDO-cts/uploads/complaints/" . $complaint['id'] . "/" . $doc['file_name']; ?>
                            <li style="margin-bottom:4px;">
                                <a href="<?php echo htmlspecialchars($url); ?>" target="_blank">
                                    <?php echo htmlspecialchars($doc['original_name']); ?>
                                </a>
                                <span style="color:#666;font-size:12px;">(<?php echo number_format($doc['file_size'] / 1024, 1); ?> KB)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
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
                    <div class="field-box involved-school-box"><?php echo htmlspecialchars($complaint['involved_school_office_unit']); ?></div>
                    
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
            <div class="attached-notice no-print">
                <strong>📎 Attached Supporting Documents:</strong>
                <ul style="margin-top:8px;">
                    <?php foreach ($documents as $index => $doc): ?>
                    <?php
                        $fileUrl = "/SDO-cts/uploads/complaints/" . $complaint['id'] . "/" . $doc['file_name'];
                        $ext = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg','jpeg','png','gif']);
                        $isPdf = ($ext === 'pdf');
                        $type = $isImage ? 'image' : ($isPdf ? 'pdf' : 'other');
                    ?>
                    <li style="margin-bottom:6px;">
                        <a href="javascript:void(0)"
                           class="doc-link"
                           data-url="<?php echo htmlspecialchars($fileUrl); ?>"
                           data-type="<?php echo $type; ?>"
                           data-name="<?php echo htmlspecialchars($doc['original_name']); ?>"
                           <?php echo $index === 0 ? 'data-default="1"' : ''; ?>>
                            <?php echo htmlspecialchars($doc['original_name']); ?>
                        </a>
                        <span style="color:#666;font-size:12px;">(<?php echo number_format($doc['file_size'] / 1024, 1); ?> KB)</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <p style="margin-top:8px;font-size:12px;color:#666;">
                    Click a file name to view the <strong>full document</strong> in the viewer below.
                    The viewer will scale the content to fit the screen (scroll if there are multiple pages).
                </p>
                <div id="docZoomToolbar" style="margin-top:8px;display:flex;align-items:center;gap:8px;font-size:12px;color:#444;">
                    <span>Zoom:</span>
                    <button type="button" class="btn btn-sm btn-outline" data-zoom="out">−</button>
                    <button type="button" class="btn btn-sm btn-outline" data-zoom="in">+</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-zoom="reset">Reset</button>
                    <span id="docZoomLabel" style="margin-left:4px;">100%</span>
                </div>
                <div id="docPreviewContainer"
                     style="margin-top:12px;width:100%;height:80vh;border:1px solid #ddd;border-radius:6px;background:#fafafa;display:flex;align-items:center;justify-content:center;overflow:auto;">
                    <div style="text-align:center;color:#777;font-size:14px;padding:20px;">
                        Select a document above to view it here as a full page.
                    </div>
                </div>
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
                <?php $allowedTransitions = $statusWorkflow[$complaint['status']] ?? []; ?>
                
                <?php if ($complaint['status'] === 'pending' && $auth->hasPermission('complaints.accept')): ?>
                <button type="button" class="btn btn-success btn-block" onclick="openActionModal('accept')">
                    <i class=""></i> Accept Complaint
                </button>
                <button type="button" class="btn btn-outline btn-block btn-danger-outline" onclick="openActionModal('return')">
                    <i class=""></i> Return Complaint
                </button>
                <?php endif; ?>
                
                <?php if (!empty($allowedTransitions) && $complaint['status'] !== 'pending'): ?>
                <button type="button" class="btn btn-primary btn-block" onclick="openStatusModal()">
                    <i class=""></i> Update Status
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
                $statusOrder = ['pending', 'accepted', 'in_progress', 'resolved', 'closed'];
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
                    <?php foreach ($history as $entry): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-status">
                                <?php echo $statusConfig[$entry['status']]['icon'] . ' ' . $statusConfig[$entry['status']]['label']; ?>
                            </div>
                            <?php if ($entry['notes']): ?>
                            <div class="timeline-notes"><?php echo htmlspecialchars($entry['notes']); ?></div>
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
            <h3>Update Status</h3>
            <button type="button" class="modal-close" onclick="closeModal('statusModal')">&times;</button>
        </div>
        <form method="POST" action="/SDO-cts/admin/api/update-status.php">
            <div class="modal-body">
                <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                
                <div class="form-group">
                    <label class="form-label">New Status</label>
                    <select name="status" class="form-control" required>
                        <?php foreach ($allowedTransitions as $status): ?>
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
            pagebreak: { 
                mode: ['avoid-all', 'css'],
                avoid: '.form-container, .additional-page'
            }
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

        // For images: create a printable page
        var printWin = window.open('', '_blank');
        if (printWin) {
            printWin.document.write('<!DOCTYPE html><html><head><title>Print</title>');
            printWin.document.write('<style>body{margin:0;padding:20px;text-align:center;}img{max-width:100%;height:auto;}</style>');
            printWin.document.write('</head><body>');
            printWin.document.write('<img src="' + PRIMARY_DOC_URL + '" onload="window.print();">');
            printWin.document.write('</body></html>');
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
    
    // Create print window
    var printWin = window.open('', '_blank', 'width=900,height=700');
    if (printWin) {
        printWin.document.write('<!DOCTYPE html>');
        printWin.document.write('<html><head><title>Print Complaint Form</title>');
        printWin.document.write(styles);
        printWin.document.write('<style>');
        printWin.document.write('body { margin: 0; padding: 20px; background: #fff; }');
        printWin.document.write('.form-container { margin: 0 auto 20px; }');
        printWin.document.write('.additional-page { margin: 20px auto; }');
        printWin.document.write('@media print { body { padding: 0; } .form-container, .additional-page { box-shadow: none !important; } }');
        printWin.document.write('</style>');
        printWin.document.write('</head><body>');
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

// Inline document preview for attached supporting documents with zoom controls
document.addEventListener('DOMContentLoaded', function () {
    // --- Uploaded main form zoom ---
    const uploadedContainer = document.getElementById('uploadedDocContainer');
    const uploadedToolbar = document.getElementById('uploadedZoomToolbar');
    const uploadedLabel = document.getElementById('uploadedZoomLabel');

    if (uploadedContainer && uploadedToolbar) {
        let upScale = 1;
        const MIN_ZOOM = 0.5;
        const MAX_ZOOM = 3;
        const ZOOM_STEP = 0.25;

        function applyUploadedZoom() {
            const inner = uploadedContainer.querySelector('.uploaded-preview-inner');
            if (inner) {
                inner.style.transform = 'scale(' + upScale + ')';
                inner.style.transformOrigin = 'top left';
            }
            if (uploadedLabel) {
                uploadedLabel.textContent = Math.round(upScale * 100) + '%';
            }
        }

        uploadedToolbar.addEventListener('click', function (e) {
            const btn = e.target.closest('button[data-zoom]');
            if (!btn) return;
            const action = btn.getAttribute('data-zoom');
            if (action === 'in') {
                upScale = Math.min(MAX_ZOOM, upScale + ZOOM_STEP);
            } else if (action === 'out') {
                upScale = Math.max(MIN_ZOOM, upScale - ZOOM_STEP);
            } else if (action === 'reset') {
                upScale = 1;
            }
            applyUploadedZoom();
        });

        // Initialize
        applyUploadedZoom();
    }

    // --- Supporting documents viewer with zoom ---
    const container = document.getElementById('docPreviewContainer');
    const links = document.querySelectorAll('.doc-link');
    const zoomToolbar = document.getElementById('docZoomToolbar');
    const zoomLabel = document.getElementById('docZoomLabel');

    if (!container || !links.length) return;

    let zoomScale = 1;
    const MIN_ZOOM = 0.5;
    const MAX_ZOOM = 3;
    const ZOOM_STEP = 0.25;

    function applyZoom() {
        const inner = container.querySelector('.doc-preview-inner');
        if (inner) {
            inner.style.transform = 'scale(' + zoomScale + ')';
            inner.style.transformOrigin = 'top left';
        }
        if (zoomLabel) {
            zoomLabel.textContent = Math.round(zoomScale * 100) + '%';
        }
    }

    function renderPreview(url, type, name) {
        if (!url) return;
        let contentHtml = '';
        if (type === 'image') {
            contentHtml = '<img src="' + url + '" alt="' + (name || 'Document image') + '" style="max-width:100%;height:auto;display:block;">';
        } else if (type === 'pdf') {
            contentHtml = '<embed src="' + url + '" type="application/pdf" style="width:100%;height:100%;border:none;" />';
        } else {
            contentHtml = '<div style="text-align:center;padding:20px;">' +
                '<p style="margin-bottom:10px;">Preview not available for this file type.</p>' +
                '<a href="' + url + '" target="_blank" class="btn btn-outline btn-sm">Open / Download</a>' +
                '</div>';
        }
        container.innerHTML = '<div class="doc-preview-inner" style="transform-origin:top left;">' + contentHtml + '</div>';
        zoomScale = 1;
        applyZoom();
    }

    links.forEach(link => {
        link.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            const type = this.getAttribute('data-type');
            const name = this.getAttribute('data-name');
            renderPreview(url, type, name);
        });
    });

    if (zoomToolbar) {
        zoomToolbar.addEventListener('click', function (e) {
            const btn = e.target.closest('button[data-zoom]');
            if (!btn) return;
            const action = btn.getAttribute('data-zoom');
            if (action === 'in') {
                zoomScale = Math.min(MAX_ZOOM, zoomScale + ZOOM_STEP);
            } else if (action === 'out') {
                zoomScale = Math.max(MIN_ZOOM, zoomScale - ZOOM_STEP);
            } else if (action === 'reset') {
                zoomScale = 1;
            }
            applyZoom();
        });
    }

    // Auto-load first document
    const first = document.querySelector('.doc-link[data-default="1"]');
    if (first) {
        renderPreview(first.getAttribute('data-url'), first.getAttribute('data-type'), first.getAttribute('data-name'));
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

