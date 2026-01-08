<?php
/**
 * SDO CTS - San Pedro Division Office Complaint Tracking Systemm
 * Main Intake Form of the Complaint-Assisted Filing System
 */

session_start();

// Clear session if requested
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    unset($_SESSION['form_data']);
    unset($_SESSION['form_files']);
    header('Location: index.php');
    exit;
}

// Store form data in session for review
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['form_data'] = $_POST;

    $tempFiles = [];
    $uploadDir = __DIR__ . '/uploads/temp/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $sessionId = session_id();
    
    // Get existing files from session
    $existingFiles = $_SESSION['form_files'] ?? [];
    
    // Check if new handwritten form is being uploaded
    $hasNewHandwrittenForm = isset($_FILES['handwritten_form']) && !empty($_FILES['handwritten_form']['name']) && $_FILES['handwritten_form']['error'] === UPLOAD_ERR_OK;
    
    // Handle exclusion of previously uploaded handwritten form
    // Only exclude if checkbox is checked AND no new handwritten form is uploaded
    $excludeHandwritten = isset($_POST['exclude_handwritten_form']) && $_POST['exclude_handwritten_form'] == '1' && !$hasNewHandwrittenForm;
    
    if (!$excludeHandwritten && !$hasNewHandwrittenForm) {
        // Keep existing handwritten_form files if not excluded and no new upload
        $existingHandwrittenFiles = array_filter($existingFiles, function($file) {
            return isset($file['category']) && $file['category'] === 'handwritten_form';
        });
        $tempFiles = array_merge($tempFiles, array_values($existingHandwrittenFiles));
    }
    
    // Reset handwritten_mode if excluded
    if ($excludeHandwritten) {
        if (isset($_SESSION['form_data']['handwritten_mode'])) {
            unset($_SESSION['form_data']['handwritten_mode']);
        }
    }
    
    // Keep other existing files (supporting docs and valid IDs) if no new uploads replace them
    if (!isset($_FILES['documents']) || empty($_FILES['documents']['name'][0])) {
        $existingSupportingFiles = array_filter($existingFiles, function($file) {
            return (!isset($file['category']) || $file['category'] === 'supporting');
        });
        $tempFiles = array_merge($tempFiles, array_values($existingSupportingFiles));
    }
    
    if (!isset($_FILES['valid_ids']) || empty($_FILES['valid_ids']['name'][0])) {
        $existingValidIdFiles = array_filter($existingFiles, function($file) {
            return isset($file['category']) && $file['category'] === 'valid_id';
        });
        $tempFiles = array_merge($tempFiles, array_values($existingValidIdFiles));
    }

    // Handle supporting documents (multiple)
    if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
        foreach ($_FILES['documents']['name'] as $key => $name) {
            if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                    $tempName = $sessionId . '_' . uniqid() . '.' . $ext;
                    $tempPath = $uploadDir . $tempName;
                    
                    if (move_uploaded_file($_FILES['documents']['tmp_name'][$key], $tempPath)) {
                        $tempFiles[] = [
                            'temp_name'      => $tempName,
                            'original_name'  => $name,
                            'type'           => $_FILES['documents']['type'][$key],
                            'size'           => $_FILES['documents']['size'][$key],
                            'category'       => 'supporting'
                        ];
                    }
                }
            }
        }
    }

    // Handle valid ID/credentials (multiple)
    if (isset($_FILES['valid_ids']) && !empty($_FILES['valid_ids']['name'][0])) {
        foreach ($_FILES['valid_ids']['name'] as $key => $name) {
            if ($_FILES['valid_ids']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                    $tempName = $sessionId . '_' . uniqid() . '.' . $ext;
                    $tempPath = $uploadDir . $tempName;
                    
                    if (move_uploaded_file($_FILES['valid_ids']['tmp_name'][$key], $tempPath)) {
                        $tempFiles[] = [
                            'temp_name'      => $tempName,
                            'original_name'  => $name,
                            'type'           => $_FILES['valid_ids']['type'][$key],
                            'size'           => $_FILES['valid_ids']['size'][$key],
                            'category'       => 'valid_id'
                        ];
                    }
                }
            }
        }
    }

    // Handle single handwritten completed form (bypass mode)
    $handwrittenAdded = false;
    if (isset($_FILES['handwritten_form']) && !empty($_FILES['handwritten_form']['name'])) {
        if ($_FILES['handwritten_form']['error'] === UPLOAD_ERR_OK) {
            $name = $_FILES['handwritten_form']['name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $tempName = $sessionId . '_' . uniqid() . '.' . $ext;
                $tempPath = $uploadDir . $tempName;

                if (move_uploaded_file($_FILES['handwritten_form']['tmp_name'], $tempPath)) {
                    $tempFiles[] = [
                        'temp_name'      => $tempName,
                        'original_name'  => $name,
                        'type'           => $_FILES['handwritten_form']['type'],
                        'size'           => $_FILES['handwritten_form']['size'],
                        'category'       => 'handwritten_form'
                    ];
                    $handwrittenAdded = true;
                }
            }
        }
    }

    // Flag in form data that this submission used a handwritten completed form (Option B)
    if ($handwrittenAdded) {
        $_SESSION['form_data']['handwritten_mode'] = 1;
    }

    // Validate that at least one valid ID/credential file is present (required)
    $validIdFilesInSubmission = array_filter($tempFiles, function($file) {
        return isset($file['category']) && $file['category'] === 'valid_id';
    });
    
    if (empty($validIdFilesInSubmission)) {
        $_SESSION['form_error'] = 'Please upload at least one valid ID or credential. This field is required.';
        header('Location: index.php');
        exit;
    }

    $_SESSION['form_files'] = $tempFiles;
    
    header('Location: review.php');
    exit;
}

// Get existing form data from session (for editing)
$formData = $_SESSION['form_data'] ?? [];
$formFiles = $_SESSION['form_files'] ?? [];
$formError = $_SESSION['form_error'] ?? null;
if (isset($_SESSION['form_error'])) {
    unset($_SESSION['form_error']);
}

// Helper function to get form value
function getValue($field, $default = '') {
    global $formData;
    return htmlspecialchars($formData[$field] ?? $default);
}

// Check if a radio is selected upon filling up
function isChecked($field, $value) {
    global $formData;
    return (isset($formData[$field]) && $formData[$field] === $value) ? 'checked' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDO CTS - Complaint Assisted Form</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="nav-bar no-print">
            <div class="nav-links">
                <a href="index.php" class="active"><i class="fas fa-file-alt"></i> File Complaint</a>
                <a href="track.php"><i class="fas fa-search"></i> Track Complaint</a>
                <a href="contact.php"><i class="fas fa-phone-alt"></i> Contact Us</a>
            </div>
        </nav>

        <!-- Header -->
        <header class="form-header">
            <div class="deped-badge">
                <i class="fas fa-landmark"></i> Department of Education Schools Division Office of San Pedro City
            </div>
            <h1>Complaints-Assisted Form</h1>
            <p class="subtitle">Region IVA - CALABARZON | Schools Division Office of San Pedro City</p>
        </header>

        <?php if ($formError): ?>
        <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <strong><i class="fas fa-exclamation-circle"></i> Error:</strong> <?php echo htmlspecialchars($formError); ?>
        </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="complaintForm" novalidate>
            <!-- Privacy Notice -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-lock"></i></span>
                    Privacy Notice
                </div>
                <div class="section-content">
                    <div class="certification-box" style="font-size: 0.9rem; line-height: 1.7;">
                        <p><strong>PRIVACY NOTICE:</strong> We collect the following personal information from you when you manually or electronically submit to us your inquiry/ies: Name, Address, E-mail address, Contact Number, ID information. The collected personal information will be utilized solely for documentation and processing of your request within DepEd and, when appropriate, endorsement to other government agency/ies that has/have jurisdiction over the subject of your inquiry. Only authorized DepEd personnel have access to this personal information, the exchange of which will be facilitated through email and/or hard copy. DepEd will only retain personal data as long as necessary for the fulfillment of the purpose.</p>
                    </div>
                </div>
            </section>

            <!-- Handwritten Form Upload (Bypass Mode) -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-file-signature"></i></span>
                    Upload Completed Complaint-Assisted Form (Photo / Scan)
                </div>
                <div class="section-content">
                    <div class="certification-box" style="font-size: 0.9rem; line-height: 1.7; margin-bottom: 1.25rem;">
                        <p>
                            If you already have a <strong>fully accomplished Complaints-Assisted Form</strong> on paper,
                            you may upload a <strong>clear photo or scanned copy</strong> of that form here.
                            When a file is uploaded in this section, you may <strong>skip filling out all the fields below</strong>.
                        </p>
                        <p style="margin-top: 0.75rem; color: var(--text-muted);">
                            Accepted formats: PDF, JPG, PNG (Max 10MB). Make sure all details and signatures on the
                            form are <strong>readable</strong>.
                        </p>
                    </div>

                    <?php 
                    $handwrittenFiles = array_filter($formFiles, function($file) {
                        return isset($file['category']) && $file['category'] === 'handwritten_form';
                    });
                    if (!empty($handwrittenFiles)): ?>
                    <div style="background: #d4edda; padding: 15px; border-radius: 8px; margin-bottom: 15px;" id="handwrittenFileSection">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                            <div style="flex: 1;">
                                <strong><i class="fas fa-file-signature"></i> Previously uploaded completed form:</strong>
                                <ul style="margin: 10px 0 0 20px;">
                                    <?php foreach ($handwrittenFiles as $file): ?>
                                    <li><?php echo htmlspecialchars($file['original_name']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger-outline" id="removeHandwrittenBtn" style="margin-left: 10px; flex-shrink: 0;">
                                <i class="fas fa-trash-alt"></i> Remove
                            </button>
                        </div>
                        <input type="hidden" name="exclude_handwritten_form" value="0" id="exclude_handwritten_form">
                        <small style="color: #155724; display: block; margin-top: 5px;">This file will be included unless you remove it or upload a new one.</small>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label" for="handwritten_form">
                            Upload Completed Form <span class="optional">(Optional)</span>
                        </label>
                        <div class="file-upload-area" id="handwrittenDropZone">
                            <div class="upload-icon"><i class="fas fa-folder-open"></i></div>
                            <p><strong>Click to upload</strong> or drag and drop your completed form here</p>
                            <p class="file-types">Accepted formats: PDF, JPG, PNG (Max 10MB)</p>
                            <input
                                type="file"
                                id="handwritten_form"
                                name="handwritten_form"
                                accept=".pdf,.jpg,.jpeg,.png"
                            >
                        </div>
                        <div class="file-list" id="handwrittenFileList"></div>
                    </div>
                </div>
            </section>
            <!-- Routing fields are for admin use only; set defaults via hidden inputs -->
            <input type="hidden" name="referred_to" value="OSDS">
            <input type="hidden" name="referred_to_other" value="">
            <input type="hidden" name="date_submitted" value="<?php echo date('Y-m-d H:i:s'); ?>">

            <!-- Section 1: Complainant Information -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-user"></i></span>
                    Complainant/Requestor Information
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label class="form-label" for="name_pangalan">
                            Name/Pangalan <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="name_pangalan" 
                               name="name_pangalan" value="<?php echo getValue('name_pangalan'); ?>"
                               placeholder="Enter your complete name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="address_tirahan">
                            Address/Tirahan <span class="required">*</span>
                        </label>
                        <textarea class="form-control" id="address_tirahan" name="address_tirahan" 
                                  placeholder="House/Unit No., Street, Barangay, City/Municipality, Province, ZIP Code" 
                                  rows="3" required><?php echo getValue('address_tirahan'); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="contact_number">
                            Contact Number <span class="required">*</span>
                        </label>
                        <input type="tel" class="form-control" id="contact_number" 
                               name="contact_number" value="<?php echo getValue('contact_number'); ?>"
                               placeholder="e.g., 09171234567" 
                               pattern="[0-9]{10,11}" required>
                        <small style="color: var(--text-muted);">Enter 10-11 digit mobile number</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email_address">
                            E-mail address <span class="required">*</span>
                        </label>
                        <input type="email" class="form-control" id="email_address" 
                               name="email_address" value="<?php echo getValue('email_address'); ?>"
                               placeholder="your.email@example.com" required>
                    </div>
                </div>
            </section>

            <!-- Section 3: Person/Office Involved -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-building"></i></span>
                    Office/School/Person Involved
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label class="form-label" for="involved_full_name">
                            Full Name <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="involved_full_name" 
                               name="involved_full_name" value="<?php echo getValue('involved_full_name'); ?>"
                               placeholder="Name of person or office involved" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="involved_position">
                            Position <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="involved_position" 
                               name="involved_position" value="<?php echo getValue('involved_position'); ?>"
                               placeholder="e.g., Teacher, Principal, Staff" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="involved_address">
                            Address <span class="required">*</span>
                        </label>
                        <textarea class="form-control" id="involved_address" name="involved_address" 
                                  placeholder="Complete address of the person/office involved" 
                                  rows="2" required><?php echo getValue('involved_address'); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="involved_school_office_unit">
                            School/Office/Unit <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="involved_school_office_unit" 
                               name="involved_school_office_unit" 
                               value="<?php echo getValue('involved_school_office_unit'); ?>"
                               placeholder="e.g., San Pedro National High School, SGOD, etc." required>
                    </div>
                </div>
            </section>

            <!-- Section 4: Complaint Details -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-edit"></i></span>
                    Narration of Complaint/Inquiry and Relief
                </div>
                <div class="section-content">
                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-style: italic;">
                        (Ano ang iyong reklamo, tanong, request o suhestiyon? Ano ang gusto mong aksiyon?)
                    </p>
                    <div class="form-group">
                        <label class="form-label" for="narration_complaint">
                            Narration of Complaint/Inquiry and Desired Action/Relief <span class="required">*</span>
                        </label>
                        <textarea class="form-control" id="narration_complaint" name="narration_complaint" 
                                  placeholder="Please provide a detailed narration of your complaint, inquiry, request, or suggestion. Include relevant dates, circumstances, and any other pertinent information. Also state the specific action or resolution you are seeking."
                                  rows="10" required><?php echo getValue('narration_complaint'); ?></textarea>
                        <small style="color: var(--text-muted);">
                            <em>(Maaaring gumamit ng mga karagdagang pahina)</em> - You may use additional pages if needed.
                        </small>
                    </div>
                    
                    <!-- Additional Page for Narration Continuation -->
                    <div class="form-group" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px dashed var(--input-border);">
                        <label class="form-label" for="narration_complaint_page2">
                            <i class="fas fa-file-alt"></i> Additional Page - Continuation of Narration <span class="optional">(Optional)</span>
                        </label>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.75rem;">
                            Use this space if you need more room for your narration. This will appear on a separate page.
                        </p>
                        <textarea class="form-control" id="narration_complaint_page2" name="narration_complaint_page2" 
                                  placeholder="Continue your narration here if you need more space..."
                                  rows="10"><?php echo getValue('narration_complaint_page2'); ?></textarea>
                    </div>
                    
                    <input type="hidden" name="desired_action_relief" value="">
                </div>
            </section>

            <!-- Section 5: Supporting Documents -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-paperclip"></i></span>
                    Supporting Documents
                </div>
                <div class="section-content">
                    <div class="certification-box" style="font-size: 0.9rem; margin-bottom: 1.5rem; padding: 1rem;">
                        <p>• Please attach your supporting documents / Certified true copies of documentary evidence and affidavits of witnesses if any.</p>
                        <p style="font-style: italic; color: var(--text-muted); margin-top: 0.5rem;">
                            (Maaaring ilakip ang inyong mga suportang dokumento/Certified True Copies ng mga dokumentaryong ebidensya at mga sinumpaang salaysay ng mga saksi, kung mayroon)
                        </p>
                    </div>
                    
                    <?php 
                    $supportingFiles = array_filter($formFiles, function($file) {
                        return (!isset($file['category']) || $file['category'] === 'supporting');
                    });
                    if (!empty($supportingFiles)): ?>
                    <div style="background: #d4edda; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <strong><i class="fas fa-paperclip"></i> Previously uploaded files:</strong>
                        <ul style="margin: 10px 0 0 20px;">
                            <?php foreach ($supportingFiles as $file): ?>
                            <li><?php echo htmlspecialchars($file['original_name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <small style="color: #155724;">These files will be included unless you upload new ones.</small>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Upload Documents <span class="optional">(Optional but recommended)</span>
                        </label>
                        <div class="file-upload-area" id="dropZone">
                            <div class="upload-icon"><i class="fas fa-folder-open"></i></div>
                            <p><strong>Click to upload</strong> or drag and drop files here</p>
                            <p class="file-types">Accepted formats: PDF, JPG, PNG (Max 10MB each)</p>
                            <input type="file" name="documents[]" id="fileInput" 
                                   accept=".pdf,.jpg,.jpeg,.png" multiple>
                        </div>
                        <div class="file-list" id="fileList"></div>
                    </div>
                </div>
            </section>

            <!-- Section 5.1: Valid ID / Credentials -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-id-card"></i></span>
                    Valid ID / Credentials
                </div>
                <div class="section-content">
                    <div class="certification-box" style="font-size: 0.9rem; margin-bottom: 1.5rem; padding: 1rem;">
                        <p>• Please attach a copy of your valid government-issued ID or credentials for verification purposes.</p>
                        <p style="font-style: italic; color: var(--text-muted); margin-top: 0.5rem;">
                            (Maaaring ilakip ang kopya ng inyong valid na ID na ibinigay ng gobyerno o mga kredensyal para sa beripikasyon)
                        </p>
                    </div>
                    
                    <?php 
                    $validIdFiles = array_filter($formFiles, function($file) {
                        return $file['category'] === 'valid_id';
                    });
                    if (!empty($validIdFiles)): ?>
                    <div style="background: #d4edda; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <strong><i class="fas fa-id-card"></i> Previously uploaded ID/Credentials:</strong>
                        <ul style="margin: 10px 0 0 20px;">
                            <?php foreach ($validIdFiles as $file): ?>
                            <li><?php echo htmlspecialchars($file['original_name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <small style="color: #155724;">These files will be included unless you upload new ones.</small>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Upload Valid ID / Credentials <span class="required">*</span>
                        </label>
                        <div class="file-upload-area" id="validIdDropZone">
                            <div class="upload-icon"><i class="fas fa-id-card"></i></div>
                            <p><strong>Click to upload</strong> or drag and drop your ID here</p>
                            <p class="file-types">Accepted formats: PDF, JPG, PNG (Max 10MB each)</p>
                            <input type="file" name="valid_ids[]" id="validIdInput" 
                                   accept=".pdf,.jpg,.jpeg,.png" multiple 
                                   data-required="true">
                        </div>
                        <div class="file-list" id="validIdFileList"></div>
                        <small style="color: var(--text-muted);">At least one valid ID or credential is required.</small>
                    </div>
                </div>
            </section>

            <!-- Section 6: Certification -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-check-square"></i></span>
                    Certification on Non-Forum Shopping
                </div>
                <div class="section-content">
                    <div class="certification-box">
                        <p><strong>CERTIFICATION ON NON-FORUM SHOPPING</strong></p>
                        <p style="margin-top: 1rem;">
                            I have not commenced any other case/action or proceedings involving the same issues 
                            before this office, other DepEd administrative and disciplinary authority/machinery, 
                            or other government agencies, and that to the best of my knowledge no such action or 
                            other DepEd administrative and disciplinary authority/machinery, or other government 
                            agencies.
                        </p>
                        <p style="margin-top: 1rem;">
                            If I should thereafter have knowledge that a similar action or proceedings has been 
                            filed or is pending before this office, other DepEd administrative and disciplinary 
                            authority/machinery, or other government agencies, I undertake to promptly inform 
                            the said offices/agencies of that fact within five (5) days therefrom.
                        </p>
                    </div>
                    
                    <div class="certification-checkbox">
                        <label>
                            <input type="checkbox" name="certification_agreed" value="1" 
                                   <?php echo isset($formData['certification_agreed']) ? 'checked' : ''; ?> required>
                            <span>I have read, understood, and agree to the above certification. I confirm that 
                            all information provided in this form is true and accurate.</span>
                        </label>
                    </div>
                </div>
            </section>

            <!-- Section 7: Signature -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon"><i class="fas fa-signature"></i></span>
                    Signature / Lagda
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label class="form-label" for="typed_signature">
                            Name / Pangalan <span class="required">*</span>
                        </label>
                        <input type="hidden" name="signature_type" value="typed">
                        <input type="text" class="form-control" id="typed_signature" name="typed_signature" 
                               value="<?php echo getValue('typed_signature'); ?>"
                               placeholder="Type your full name as digital signature"
                               style="font-family: 'Arial', Times, serif; font-size: 1rem;" required>
                        <small style="color: var(--text-muted);"></small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Date Signed</label>
                        <input type="text" class="form-control" value="<?php echo date('F j, Y'); ?>" readonly 
                               style="background: #e9ecef; cursor: not-allowed;">
                        <input type="hidden" name="date_signed" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
            </section>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-redo"></i> Reset Form
                </button>
                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                    Review Submission <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>

        <!-- Footer -->
        <footer class="form-footer">
            <p>SDO CTS - San Pedro Division Office Complaint Tracking System</p>
            <p>Department of Education - San Pedro Division</p>
        </footer>
    </div>

    <!-- Reset Confirmation Modal -->
    <div class="custom-modal" id="resetModal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Reset Form</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reset the form? All entered data will be lost.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelResetBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmResetBtn">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Remove Handwritten Form Confirmation Modal -->
    <div class="custom-modal" id="removeHandwrittenModal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                <i class="fas fa-trash-alt"></i>
                <h3>Remove Completed Form</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this completed form file?</p>
                <p style="margin-top: 0.75rem; color: var(--text-muted);">This file will <strong>not be included</strong> in your submission. You can upload a new file or fill out the form manually instead.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelRemoveHandwrittenBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmRemoveHandwrittenBtn">
                    <i class="fas fa-trash-alt"></i> Remove File
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/form.js?v=<?php echo time(); ?>"></script>
    <script>
    // Handle remove handwritten form button
    document.addEventListener('DOMContentLoaded', function() {
        const removeBtn = document.getElementById('removeHandwrittenBtn');
        const excludeInput = document.getElementById('exclude_handwritten_form');
        const fileSection = document.getElementById('handwrittenFileSection');
        const modal = document.getElementById('removeHandwrittenModal');
        
        if (removeBtn && modal) {
            removeBtn.addEventListener('click', function() {
                // Prevent body scroll when modal is open
                document.body.style.overflow = 'hidden';
                
                // Show modal
                modal.classList.add('active');
                
                // Handle confirm button
                const confirmBtn = document.getElementById('confirmRemoveHandwrittenBtn');
                const cancelBtn = document.getElementById('cancelRemoveHandwrittenBtn');
                const overlay = modal.querySelector('.modal-overlay');
                
                const closeModal = () => {
                    modal.classList.remove('active');
                    // Restore body scroll
                    document.body.style.overflow = '';
                    // Clean up event listeners
                    confirmBtn.onclick = null;
                    cancelBtn.onclick = null;
                    if (overlay) overlay.onclick = null;
                };
                
                const handleConfirm = () => {
                    closeModal();
                    // Mark file as excluded
                    if (excludeInput) {
                        excludeInput.value = '1';
                    }
                    // Visual feedback
                    if (fileSection) {
                        fileSection.style.opacity = '0.5';
                        fileSection.style.pointerEvents = 'none';
                    }
                    removeBtn.innerHTML = '<i class="fas fa-check"></i> Removed';
                    removeBtn.disabled = true;
                    removeBtn.classList.remove('btn-danger-outline');
                    removeBtn.classList.add('btn-secondary');
                };
                
                // Set up event listeners
                confirmBtn.onclick = handleConfirm;
                cancelBtn.onclick = closeModal;
                if (overlay) {
                    overlay.onclick = closeModal;
                }
                
                // Close on Escape key
                const handleEscape = (e) => {
                    if (e.key === 'Escape' && modal.classList.contains('active')) {
                        closeModal();
                        document.removeEventListener('keydown', handleEscape);
                    }
                };
                document.addEventListener('keydown', handleEscape);
            });
        }
    });
    </script>
</body>
</html>
