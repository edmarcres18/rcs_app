/**
 * Avatar Upload Enhancement Script
 * Provides client-side validation, preview, and progress indication
 */

document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatar-preview');
    const uploadProgress = document.getElementById('upload-progress');
    const profileForm = document.getElementById('profile-form');
    const submitButton = document.querySelector('button[type="submit"]');
    
    if (!avatarInput) return;

    // File size limit (10MB)
    const MAX_FILE_SIZE = 10 * 1024 * 1024;
    
    // Allowed file types
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];

    /**
     * Validate file before upload
     */
    function validateFile(file) {
        const errors = [];

        // Check file size
        if (file.size > MAX_FILE_SIZE) {
            errors.push(`File size (${formatFileSize(file.size)}) exceeds the maximum limit of 10MB.`);
        }

        // Check file type
        if (!ALLOWED_TYPES.includes(file.type)) {
            errors.push('Please select a valid image file (JPEG, PNG, JPG, GIF, or WebP).');
        }

        return errors;
    }

    /**
     * Format file size for display
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Show file preview
     */
    function showPreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (avatarPreview) {
                avatarPreview.src = e.target.result;
                avatarPreview.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }

    /**
     * Show error messages
     */
    function showErrors(errors) {
        // Remove existing error messages
        const existingErrors = document.querySelectorAll('.avatar-error');
        existingErrors.forEach(error => error.remove());

        // Add new error messages
        errors.forEach(error => {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'avatar-error alert alert-danger mt-2';
            errorDiv.textContent = error;
            avatarInput.parentNode.appendChild(errorDiv);
        });
    }

    /**
     * Clear error messages
     */
    function clearErrors() {
        const existingErrors = document.querySelectorAll('.avatar-error');
        existingErrors.forEach(error => error.remove());
    }

    /**
     * Show upload progress
     */
    function showProgress() {
        if (uploadProgress) {
            uploadProgress.style.display = 'block';
            uploadProgress.innerHTML = `
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 100%">
                        Uploading avatar...
                    </div>
                </div>
            `;
        }
    }

    /**
     * Hide upload progress
     */
    function hideProgress() {
        if (uploadProgress) {
            uploadProgress.style.display = 'none';
        }
    }

    // File input change handler
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        clearErrors();
        
        if (!file) {
            if (avatarPreview) {
                avatarPreview.style.display = 'none';
            }
            return;
        }

        // Validate file
        const errors = validateFile(file);
        
        if (errors.length > 0) {
            showErrors(errors);
            avatarInput.value = ''; // Clear the input
            if (avatarPreview) {
                avatarPreview.style.display = 'none';
            }
            return;
        }

        // Show preview
        showPreview(file);
        
        // Show file info
        const fileInfo = document.createElement('div');
        fileInfo.className = 'avatar-info alert alert-info mt-2';
        fileInfo.innerHTML = `
            <strong>Selected file:</strong> ${file.name}<br>
            <strong>Size:</strong> ${formatFileSize(file.size)}<br>
            <strong>Type:</strong> ${file.type}
        `;
        
        // Remove existing file info
        const existingInfo = document.querySelector('.avatar-info');
        if (existingInfo) {
            existingInfo.remove();
        }
        
        avatarInput.parentNode.appendChild(fileInfo);
    });

    // Form submission handler
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const file = avatarInput.files[0];
            
            if (file) {
                // Final validation before submit
                const errors = validateFile(file);
                
                if (errors.length > 0) {
                    e.preventDefault();
                    showErrors(errors);
                    return false;
                }
                
                // Show progress and disable submit button
                showProgress();
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Uploading...';
                }
            }
        });
    }

    // Drag and drop functionality
    const dropZone = document.getElementById('avatar-drop-zone');
    
    if (dropZone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('drag-over');
        }

        function unhighlight(e) {
            dropZone.classList.remove('drag-over');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                avatarInput.files = files;
                avatarInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }
});
