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

    // If the routing section is not present (public form without unit selection),
    // skip initializing radio option behavior.
    if (!radioOptions.length || !otherWrapper || !otherInput) {
        return;
    }

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
    const form = document.getElementById('complaintForm');
    
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

    // --- Handwritten completed form upload (single file, same visual style) ---
    const hwDropZone = document.getElementById('handwrittenDropZone');
    const hwInput = document.getElementById('handwritten_form');
    const hwFileList = document.getElementById('handwrittenFileList');

    if (hwDropZone && hwInput && hwFileList) {
        let handwrittenFile = null;

        hwDropZone.addEventListener('click', () => hwInput.click());

        hwDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            hwDropZone.classList.add('dragover');
        });

        hwDropZone.addEventListener('dragleave', () => {
            hwDropZone.classList.remove('dragover');
        });

        hwDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            hwDropZone.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                handleHandwrittenFile(e.dataTransfer.files[0]);
            }
        });

        hwInput.addEventListener('change', () => {
            if (hwInput.files && hwInput.files.length > 0) {
                handleHandwrittenFile(hwInput.files[0]);
            }
        });

        function handleHandwrittenFile(file) {
            // Validate type
            if (!allowedTypes.includes(file.type)) {
                alert(`Invalid file type: ${file.name}. Only PDF, JPG, and PNG files are allowed.`);
                hwInput.value = '';
                handwrittenFile = null;
                updateHandwrittenList();
                return;
            }

            // Validate size
            if (file.size > maxFileSize) {
                alert(`File too large: ${file.name}. Maximum size is 10MB.`);
                hwInput.value = '';
                handwrittenFile = null;
                updateHandwrittenList();
                return;
            }

            handwrittenFile = file;
            updateHandwrittenList();

            // Automatically proceed to review when a valid handwritten form is attached
            // (bypass all other fields – Option B). Let the existing submit handler
            // handle validation/redirect logic.
            if (form) {
                form.submit();
            }
        }

        function updateHandwrittenList() {
            hwFileList.innerHTML = '';

            if (!handwrittenFile) return;

            const item = document.createElement('div');
            item.className = 'file-item';

            const icon = handwrittenFile.type === 'application/pdf' ? '📄' : '🖼️';
            const size = formatFileSize(handwrittenFile.size);

            item.innerHTML = `
                <span class="file-name">${icon} ${handwrittenFile.name} <small>(${size})</small></span>
                <span class="remove-file" data-role="remove-handwritten">✕ Remove</span>
            `;

            hwFileList.appendChild(item);

            const removeBtn = item.querySelector('[data-role="remove-handwritten"]');
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    handwrittenFile = null;
                    hwInput.value = '';
                    updateHandwrittenList();
                });
            }
        }
    }

    // --- Valid ID / Credentials upload (multiple files) ---
    const validIdDropZone = document.getElementById('validIdDropZone');
    const validIdInput = document.getElementById('validIdInput');
    const validIdFileList = document.getElementById('validIdFileList');

    if (validIdDropZone && validIdInput && validIdFileList) {
        let selectedValidIdFiles = [];

        validIdDropZone.addEventListener('click', () => validIdInput.click());

        validIdDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            validIdDropZone.classList.add('dragover');
        });

        validIdDropZone.addEventListener('dragleave', () => {
            validIdDropZone.classList.remove('dragover');
        });

        validIdDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            validIdDropZone.classList.remove('dragover');
            handleValidIdFiles(e.dataTransfer.files);
        });

        validIdInput.addEventListener('change', () => {
            handleValidIdFiles(validIdInput.files);
        });

        function handleValidIdFiles(files) {
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
                if (selectedValidIdFiles.some(f => f.name === file.name && f.size === file.size)) {
                    return;
                }

                selectedValidIdFiles.push(file);
            });

            updateValidIdFileList();
            updateValidIdInput();
        }

        function updateValidIdFileList() {
            validIdFileList.innerHTML = '';

            selectedValidIdFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';

                const icon = file.type === 'application/pdf' ? '📄' : '🖼️';
                const size = formatFileSize(file.size);

                fileItem.innerHTML = `
                    <span class="file-name">${icon} ${file.name} <small>(${size})</small></span>
                    <span class="remove-file" data-validid-index="${index}">✕ Remove</span>
                `;

                validIdFileList.appendChild(fileItem);
            });

            // Add remove handlers
            validIdFileList.querySelectorAll('.remove-file[data-validid-index]').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.dataset.validIdIndex || this.dataset.validIndex || this.getAttribute('data-validid-index'));
                    selectedValidIdFiles.splice(index, 1);
                    updateValidIdFileList();
                    updateValidIdInput();
                });
            });
        }

        function updateValidIdInput() {
            const dt = new DataTransfer();
            selectedValidIdFiles.forEach(file => dt.items.add(file));
            validIdInput.files = dt.files;
        }
    }
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const form = document.getElementById('complaintForm');
    if (!form) return;
    const handwrittenInput = document.getElementById('handwritten_form');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const hasHandwritten =
            handwrittenInput &&
            handwrittenInput.files &&
            handwrittenInput.files.length > 0;
        
        // Clear previous errors
        document.querySelectorAll('.form-control.error').forEach(el => {
            el.classList.remove('error');
        });
        document.querySelectorAll('.error-message').forEach(el => {
            el.remove();
        });

        // If a completed handwritten form is uploaded, bypass all other field validations (Option B)
        if (!hasHandwritten) {
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
            // After clearing session, reload the page with clear flag
            // so that ALL fields (including file inputs and any client-side state)
            // are fully reset to their initial empty state.
            window.location.href = 'index.php?clear=1';
        })
        .catch(() => {
            // Fallback: just reload the page with clear parameter
            window.location.href = 'index.php?clear=1';
        });
}
