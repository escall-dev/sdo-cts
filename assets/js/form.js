/**
 * SDO CTS - Form JavaScript
 * Handles form validation and file uploads
 */

document.addEventListener('DOMContentLoaded', function() {
    initRadioOptions();
    initFileUpload();
    initFormValidation();
});

/**
 * Initialize radio button styling and "Others" field toggle
 */
function initRadioOptions() {
    const radioOptions = document.querySelectorAll('.radio-option');
    const otherWrapper = document.getElementById('otherReferredWrapper');
    const otherInput = document.querySelector('input[name="referred_to_other"]');
    
    radioOptions.forEach(option => {
        const input = option.querySelector('input[type="radio"]');
        
        input.addEventListener('change', function() {
            // Update selected state
            radioOptions.forEach(opt => opt.classList.remove('selected'));
            if (this.checked) {
                option.classList.add('selected');
            }
            
            // Toggle "Others" input
            if (this.value === 'Others') {
                otherWrapper.classList.add('visible');
                otherInput.required = true;
                otherInput.focus();
            } else {
                otherWrapper.classList.remove('visible');
                otherInput.required = false;
                otherInput.value = '';
            }
        });
    });
}

/**
 * Initialize file upload with drag & drop
 */
function initFileUpload() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    
    if (!dropZone || !fileInput) return;
    
    let selectedFiles = [];
    const maxFileSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    
    // Click to upload
    dropZone.addEventListener('click', () => fileInput.click());
    
    // Drag & drop handlers
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });
    
    // File input change
    fileInput.addEventListener('change', () => {
        handleFiles(fileInput.files);
    });
    
    function handleFiles(files) {
        Array.from(files).forEach(file => {
            // Validate file type
            if (!allowedTypes.includes(file.type)) {
                alert(`Invalid file type: ${file.name}. Only PDF, JPG, and PNG files are allowed.`);
                return;
            }
            
            // Validate file size
            if (file.size > maxFileSize) {
                alert(`File too large: ${file.name}. Maximum size is 10MB.`);
                return;
            }
            
            // Check for duplicates
            if (selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                return;
            }
            
            selectedFiles.push(file);
        });
        
        updateFileList();
        updateFileInput();
    }
    
    function updateFileList() {
        fileList.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            
            const icon = file.type === 'application/pdf' ? '📄' : '🖼️';
            const size = formatFileSize(file.size);
            
            fileItem.innerHTML = `
                <span class="file-name">${icon} ${file.name} <small>(${size})</small></span>
                <span class="remove-file" data-index="${index}">✕ Remove</span>
            `;
            
            fileList.appendChild(fileItem);
        });
        
        // Add remove handlers
        document.querySelectorAll('.remove-file').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                selectedFiles.splice(index, 1);
                updateFileList();
                updateFileInput();
            });
        });
    }
    
    function updateFileInput() {
        // Create a new DataTransfer to update the file input
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const form = document.getElementById('complaintForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Clear previous errors
        document.querySelectorAll('.form-control.error').forEach(el => {
            el.classList.remove('error');
        });
        document.querySelectorAll('.error-message').forEach(el => {
            el.remove();
        });
        
        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (field.type === 'checkbox') {
                if (!field.checked) {
                    isValid = false;
                    showError(field.parentElement.parentElement, 'You must agree to the certification');
                }
            } else if (field.type === 'radio') {
                const radioGroup = form.querySelectorAll(`input[name="${field.name}"]`);
                const isChecked = Array.from(radioGroup).some(r => r.checked);
                if (!isChecked) {
                    isValid = false;
                    showError(field.closest('.form-group'), 'Please select an option');
                }
            } else if (!field.value.trim()) {
                isValid = false;
                field.classList.add('error');
                showError(field, 'This field is required');
            }
        });
        
        // Validate email
        const emailField = document.getElementById('email_address');
        if (emailField && emailField.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailField.value)) {
                isValid = false;
                emailField.classList.add('error');
                showError(emailField, 'Please enter a valid email address');
            }
        }
        
        // Validate contact number
        const contactField = document.getElementById('contact_number');
        if (contactField && contactField.value) {
            const phoneRegex = /^[0-9]{10,11}$/;
            if (!phoneRegex.test(contactField.value.replace(/\D/g, ''))) {
                isValid = false;
                contactField.classList.add('error');
                showError(contactField, 'Please enter a valid 10-11 digit phone number');
            }
        }
        
        // Validate typed signature
        const typedSig = document.getElementById('typed_signature');
        if (typedSig && !typedSig.value.trim()) {
            isValid = false;
            typedSig.classList.add('error');
            showError(typedSig, 'Please type your signature');
        }
        
        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = document.querySelector('.form-control.error, .error-message');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    // Remove error on input
    form.querySelectorAll('.form-control').forEach(field => {
        field.addEventListener('input', function() {
            this.classList.remove('error');
            const errorMsg = this.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        });
    });
}

function showError(element, message) {
    const existingError = element.parentElement.querySelector('.error-message');
    if (existingError) return;
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    if (element.parentElement) {
        element.parentElement.appendChild(errorDiv);
    }
}

/**
 * Reset form and clear session
 */
function resetForm() {
    const modal = document.getElementById('resetModal');
    if (!modal) {
        // Fallback to confirm if modal doesn't exist
        if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
            performReset();
        }
        return;
    }
    
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
    
    // Show modal
    modal.classList.add('active');
    
    // Handle confirm button
    const confirmBtn = document.getElementById('confirmResetBtn');
    const cancelBtn = document.getElementById('cancelResetBtn');
    const overlay = modal.querySelector('.modal-overlay');
    
    const closeModal = () => {
        modal.classList.remove('active');
        // Restore body scroll
        document.body.style.overflow = '';
        // Clean up event listeners
        confirmBtn.onclick = null;
        cancelBtn.onclick = null;
        overlay.onclick = null;
    };
    
    const handleConfirm = () => {
        closeModal();
        performReset();
    };
    
    // Set up event listeners
    confirmBtn.onclick = handleConfirm;
    cancelBtn.onclick = closeModal;
    overlay.onclick = closeModal;
    
    // Close on Escape key
    const handleEscape = (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
            document.removeEventListener('keydown', handleEscape);
        }
    };
    document.addEventListener('keydown', handleEscape);
}

/**
 * Perform the actual form reset
 */
function performReset() {
    // Clear session data via AJAX
    fetch('clear_session.php', { method: 'POST' })
        .then(() => {
            // Reset the form
            document.getElementById('complaintForm').reset();
            
            // Clear file list
            const fileList = document.getElementById('fileList');
            if (fileList) fileList.innerHTML = '';
            
            // Clear radio selections
            document.querySelectorAll('.radio-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Hide "Others" input
            const otherWrapper = document.getElementById('otherReferredWrapper');
            if (otherWrapper) otherWrapper.classList.remove('visible');
            
            // Clear errors
            document.querySelectorAll('.form-control.error').forEach(el => {
                el.classList.remove('error');
            });
            document.querySelectorAll('.error-message').forEach(el => {
                el.remove();
            });
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .catch(() => {
            // Fallback: just reload the page with clear parameter
            window.location.href = 'index.php?clear=1';
        });
}
