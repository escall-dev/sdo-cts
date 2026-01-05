<?php
/**
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 * Main Intake Form
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
    $_SESSION['form_files'] = [];
    
    // Handle file uploads
    if (!empty($_FILES['documents']['name'][0])) {
        $uploadDir = __DIR__ . '/uploads/temp/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $sessionId = session_id();
        $tempFiles = [];
        
        foreach ($_FILES['documents']['name'] as $key => $name) {
            if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                    $tempName = $sessionId . '_' . uniqid() . '.' . $ext;
                    $tempPath = $uploadDir . $tempName;
                    
                    if (move_uploaded_file($_FILES['documents']['tmp_name'][$key], $tempPath)) {
                        $tempFiles[] = [
                            'temp_name' => $tempName,
                            'original_name' => $name,
                            'type' => $_FILES['documents']['type'][$key],
                            'size' => $_FILES['documents']['size'][$key]
                        ];
                    }
                }
            }
        }
        $_SESSION['form_files'] = $tempFiles;
    }
    
    header('Location: review.php');
    exit;
}

// Get existing form data from session (for editing)
$formData = $_SESSION['form_data'] ?? [];
$formFiles = $_SESSION['form_files'] ?? [];

// Helper function to get form value
function getValue($field, $default = '') {
    global $formData;
    return htmlspecialchars($formData[$field] ?? $default);
}

// Check if a radio is selected
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="nav-bar no-print">
            <div class="nav-links">
                <a href="index.php" class="active">📝 File Complaint</a>
                <a href="track.php">🔍 Track Complaint</a>
            </div>
        </nav>

        <!-- Header -->
        <header class="form-header">
            <div class="deped-badge">
                🏛️ Department of Education
            </div>
            <h1>Complaints-Assisted Form</h1>
            <p class="subtitle">Region IVA - CALABARZON | Schools Division Office of San Pedro City</p>
        </header>

        <!-- Privacy Notice -->
        <section class="form-section">
            <div class="section-header">
                <span class="section-icon">🔒</span>
                Privacy Notice
            </div>
            <div class="section-content">
                <div class="certification-box" style="font-size: 0.9rem; line-height: 1.7;">
                    <p><strong>PRIVACY NOTICE:</strong> We collect the following personal information from you when you manually or electronically submit to us your inquiry/ies: Name, Address, E-mail address, Contact Number, ID information. The collected personal information will be utilized solely for documentation and processing of your request within DepEd and, when appropriate, endorsement to other government agency/ies that has/have jurisdiction over the subject of your inquiry. Only authorized DepEd personnel have access to this personal information, the exchange of which will be facilitated through email and/or hard copy. DepEd will only retain personal data as long as necessary for the fulfillment of the purpose.</p>
                </div>
            </div>
        </section>

        <form action="" method="POST" enctype="multipart/form-data" id="complaintForm" novalidate>
            <!-- Section 1: Routing and Reference -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon">📋</span>
                    Referred to (indicate unit/section)
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label class="form-label">
                            Select Unit/Section <span class="required">*</span>
                        </label>
                        <div class="radio-group">
                            <label class="radio-option <?php echo isChecked('referred_to', 'OSDS') ? 'selected' : ''; ?>">
                                <input type="radio" name="referred_to" value="OSDS" <?php echo isChecked('referred_to', 'OSDS'); ?> required>
                                <span class="radio-label">
                                    <strong>OSDS</strong> - Office of the Schools Division Superintendent
                                </span>
                            </label>
                            <label class="radio-option <?php echo isChecked('referred_to', 'SGOD') ? 'selected' : ''; ?>">
                                <input type="radio" name="referred_to" value="SGOD" <?php echo isChecked('referred_to', 'SGOD'); ?> required>
                                <span class="radio-label">
                                    <strong>SGOD</strong> - School Governance and Operations Division
                                </span>
                            </label>
                            <label class="radio-option <?php echo isChecked('referred_to', 'CID') ? 'selected' : ''; ?>">
                                <input type="radio" name="referred_to" value="CID" <?php echo isChecked('referred_to', 'CID'); ?> required>
                                <span class="radio-label">
                                    <strong>CID</strong> - Curriculum Implementation Division
                                </span>
                            </label>
                            <label class="radio-option <?php echo isChecked('referred_to', 'Others') ? 'selected' : ''; ?>">
                                <input type="radio" name="referred_to" value="Others" <?php echo isChecked('referred_to', 'Others'); ?> required>
                                <span class="radio-label">
                                    <strong>Others:</strong> Please specify below
                                </span>
                            </label>
                        </div>
                        <div class="other-input-wrapper <?php echo isChecked('referred_to', 'Others') ? 'visible' : ''; ?>" id="otherReferredWrapper">
                            <input type="text" class="form-control" name="referred_to_other" 
                                   value="<?php echo getValue('referred_to_other'); ?>"
                                   placeholder="Please specify the office or unit">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Date/Petsa</label>
                        <input type="text" class="form-control" value="<?php echo date('F j, Y'); ?>" readonly 
                               style="background: #e9ecef; cursor: not-allowed;">
                        <input type="hidden" name="date_submitted" value="<?php echo date('Y-m-d H:i:s'); ?>">
                    </div>
                </div>
            </section>

            <!-- Section 2: Complainant Information -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon">👤</span>
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
                    <span class="section-icon">🏢</span>
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
                    <span class="section-icon">📝</span>
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
                    <input type="hidden" name="desired_action_relief" value="">
                </div>
            </section>

            <!-- Section 5: Supporting Documents -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon">📎</span>
                    Supporting Documents
                </div>
                <div class="section-content">
                    <div class="certification-box" style="font-size: 0.9rem; margin-bottom: 1.5rem; padding: 1rem;">
                        <p>• Please attach your supporting documents / Certified true copies of documentary evidence and affidavits of witnesses if any.</p>
                        <p style="font-style: italic; color: var(--text-muted); margin-top: 0.5rem;">
                            (Maaaring ilakip ang inyong mga suportang dokumento/Certified True Copies ng mga dokumentaryong ebidensya at mga sinumpaang salaysay ng mga saksi, kung mayroon)
                        </p>
                    </div>
                    
                    <?php if (!empty($formFiles)): ?>
                    <div style="background: #d4edda; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <strong>📎 Previously uploaded files:</strong>
                        <ul style="margin: 10px 0 0 20px;">
                            <?php foreach ($formFiles as $file): ?>
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
                            <div class="upload-icon">📁</div>
                            <p><strong>Click to upload</strong> or drag and drop files here</p>
                            <p class="file-types">Accepted formats: PDF, JPG, PNG (Max 10MB each)</p>
                            <input type="file" name="documents[]" id="fileInput" 
                                   accept=".pdf,.jpg,.jpeg,.png" multiple>
                        </div>
                        <div class="file-list" id="fileList"></div>
                    </div>
                </div>
            </section>

            <!-- Section 6: Certification -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-icon">✅</span>
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
                    <span class="section-icon">✍️</span>
                    Name and Signature / Pangalan at Lagda
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label class="form-label" for="printed_name_pangalan">
                           Name / Pangalan <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="printed_name_pangalan" 
                               name="printed_name_pangalan" value="<?php echo getValue('printed_name_pangalan'); ?>"
                               placeholder="Your printed name as acknowledgment" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="typed_signature">
                            Signature (Type your name) <span class="required">*</span>
                        </label>
                        <input type="hidden" name="signature_type" value="typed">
                        <input type="text" class="form-control" id="typed_signature" name="typed_signature" 
                               value="<?php echo getValue('typed_signature'); ?>"
                               placeholder="Type your full name as digital signature"
                               style="font-family: 'Brush Script MT', cursive; font-size: 1.5rem;" required>
                        <small style="color: var(--text-muted);">Your typed name serves as your digital signature</small>
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
                    🔄 Reset Form
                </button>
                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                    Review Submission ➡️
                </button>
            </div>
        </form>

        <!-- Footer -->
        <footer class="form-footer">
            <p>SDO CTS - San Pedro Division Office Complaint Tracking System</p>
            <p>Department of Education - San Pedro Division</p>
        </footer>
    </div>

    <script src="assets/js/form.js"></script>
</body>
</html>
