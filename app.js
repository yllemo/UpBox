// DOM elements
const fileInput = document.getElementById('fileInput');
const uploadForm = document.getElementById('uploadForm');
const fileInputLabel = document.querySelector('.file-input-label');
const fileInputText = document.querySelector('.file-input-text');

// Initialize app
document.addEventListener('DOMContentLoaded', function() {
    // File input handling
    if (fileInput && fileInputLabel && fileInputText) {
        fileInput.addEventListener('change', handleFileSelection);
        
        // Drag and drop functionality
        fileInputLabel.addEventListener('dragover', handleDragOver);
        fileInputLabel.addEventListener('dragleave', handleDragLeave);
        fileInputLabel.addEventListener('drop', handleFileDrop);
    }
    
    // Form submission handling
    if (uploadForm) {
        uploadForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Auto-hide messages after 5 seconds
    hideMessagesAfterDelay();
    
    // Add keyboard shortcuts
    addKeyboardShortcuts();
});

// File selection handler
function handleFileSelection(event) {
    const file = event.target.files[0];
    updateFileInputDisplay(file);
}

// Update file input display
function updateFileInputDisplay(file) {
    if (file) {
        const fileSize = formatFileSize(file.size);
        const fileName = file.name;
        
        fileInputText.textContent = `${fileName} (${fileSize})`;
        fileInputLabel.parentElement.classList.add('file-selected');
        
        // Validate file type
        const allowedExtensions = ['html', 'svg', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'md', 'txt'];
        const fileExtension = fileName.split('.').pop().toLowerCase();
        
        if (!allowedExtensions.includes(fileExtension)) {
            showMessage('Invalid file type. Please select a valid file.', 'error');
            fileInput.value = '';
            resetFileInputDisplay();
            return;
        }
        
        // Validate file size (10MB limit)
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            showMessage('File size exceeds 10MB limit.', 'error');
            fileInput.value = '';
            resetFileInputDisplay();
            return;
        }
        
    } else {
        resetFileInputDisplay();
    }
}

// Reset file input display
function resetFileInputDisplay() {
    fileInputText.textContent = 'Choose File';
    fileInputLabel.parentElement.classList.remove('file-selected');
}

// Drag and drop handlers
function handleDragOver(event) {
    event.preventDefault();
    event.stopPropagation();
    fileInputLabel.style.borderColor = '#64b5f6';
    fileInputLabel.style.background = 'rgba(100, 181, 246, 0.2)';
}

function handleDragLeave(event) {
    event.preventDefault();
    event.stopPropagation();
    fileInputLabel.style.borderColor = 'rgba(100, 181, 246, 0.5)';
    fileInputLabel.style.background = 'rgba(255, 255, 255, 0.05)';
}

function handleFileDrop(event) {
    event.preventDefault();
    event.stopPropagation();
    
    handleDragLeave(event);
    
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        updateFileInputDisplay(files[0]);
    }
}

// Form submission handler
function handleFormSubmit(event) {
    const submitButton = uploadForm.querySelector('button[type="submit"]');
    
    if (!fileInput.files || fileInput.files.length === 0) {
        event.preventDefault();
        showMessage('Please select a file to upload.', 'error');
        return;
    }
    
    // Add uploading class for visual feedback
    uploadForm.classList.add('uploading');
    submitButton.disabled = true;
    submitButton.textContent = 'Uploading';
    
    // Note: The actual upload is handled by PHP, so we don't prevent default here
}

// Show message function
function showMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.dynamic-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `${type} dynamic-message`;
    messageDiv.textContent = message;
    
    // Insert after upload info
    const uploadInfo = document.querySelector('.upload-info');
    if (uploadInfo) {
        uploadInfo.parentNode.insertBefore(messageDiv, uploadInfo.nextSibling);
    }
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 5000);
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Hide messages after delay
function hideMessagesAfterDelay() {
    const messages = document.querySelectorAll('.success, .error');
    messages.forEach(message => {
        if (!message.classList.contains('dynamic-message')) {
            setTimeout(() => {
                message.style.opacity = '0';
                message.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    if (message.parentNode) {
                        message.remove();
                    }
                }, 300);
            }, 5000);
        }
    });
}

// Keyboard shortcuts
function addKeyboardShortcuts() {
    document.addEventListener('keydown', function(event) {
        // Ctrl/Cmd + U to focus file input
        if ((event.ctrlKey || event.metaKey) && event.key === 'u') {
            event.preventDefault();
            if (fileInput) {
                fileInput.click();
            }
        }
        
        // Escape to clear file selection
        if (event.key === 'Escape') {
            if (fileInput && fileInput.files.length > 0) {
                fileInput.value = '';
                resetFileInputDisplay();
                showMessage('File selection cleared.', 'success');
            }
        }
    });
}

// File preview functionality (for images)
function previewFile(file) {
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Create or update preview
            let preview = document.getElementById('file-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'file-preview';
                preview.innerHTML = `
                    <h3>Preview:</h3>
                    <img id="preview-image" style="max-width: 200px; max-height: 200px; border-radius: 8px; margin-top: 10px;">
                `;
                fileInputLabel.parentElement.appendChild(preview);
            }
            
            const previewImg = document.getElementById('preview-image');
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        // Remove preview if not an image
        const preview = document.getElementById('file-preview');
        if (preview) {
            preview.style.display = 'none';
        }
    }
}

// Enhanced file selection with preview
if (fileInput) {
    fileInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        updateFileInputDisplay(file);
        previewFile(file);
    });
}

// Add smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add loading animation for file operations
function showLoadingSpinner(show = true) {
    let spinner = document.getElementById('loading-spinner');
    
    if (show && !spinner) {
        spinner = document.createElement('div');
        spinner.id = 'loading-spinner';
        spinner.innerHTML = `
            <div class="spinner-overlay">
                <div class="spinner"></div>
                <p>Processing...</p>
            </div>
        `;
        document.body.appendChild(spinner);
        
        // Add spinner styles dynamically
        const style = document.createElement('style');
        style.textContent = `
            #loading-spinner {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
            .spinner-overlay {
                text-align: center;
                color: #e0e0e0;
            }
            .spinner {
                width: 40px;
                height: 40px;
                border: 4px solid rgba(100, 181, 246, 0.3);
                border-top: 4px solid #64b5f6;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 15px;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    } else if (!show && spinner) {
        spinner.remove();
    }
}

// File validation with detailed feedback
function validateFile(file) {
    const errors = [];
    const allowedTypes = {
        'html': 'text/html',
        'svg': 'image/svg+xml',
        'jpg': 'image/jpeg',
        'jpeg': 'image/jpeg',
        'png': 'image/png',
        'gif': 'image/gif',
        'webp': 'image/webp',
        'md': 'text/markdown',
        'txt': 'text/plain'
    };
    
    const fileExtension = file.name.split('.').pop().toLowerCase();
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    // Check file extension
    if (!Object.keys(allowedTypes).includes(fileExtension)) {
        errors.push(`File type ".${fileExtension}" is not allowed`);
    }
    
    // Check file size
    if (file.size > maxSize) {
        errors.push(`File size (${formatFileSize(file.size)}) exceeds 10MB limit`);
    }
    
    // Check if file is empty
    if (file.size === 0) {
        errors.push('File appears to be empty');
    }
    
    return {
        valid: errors.length === 0,
        errors: errors
    };
}

// Enhanced drag and drop with multiple file handling
function setupAdvancedDragDrop() {
    if (!fileInputLabel) return;
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        fileInputLabel.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    // Highlight drop area
    ['dragenter', 'dragover'].forEach(eventName => {
        fileInputLabel.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        fileInputLabel.addEventListener(eventName, unhighlight, false);
    });
    
    // Handle dropped files
    fileInputLabel.addEventListener('drop', handleDrop, false);
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight() {
        fileInputLabel.classList.add('drag-highlight');
    }
    
    function unhighlight() {
        fileInputLabel.classList.remove('drag-highlight');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 1) {
            showMessage('Please drop only one file at a time.', 'error');
            return;
        }
        
        if (files.length === 1) {
            const validation = validateFile(files[0]);
            if (validation.valid) {
                fileInput.files = files;
                updateFileInputDisplay(files[0]);
                previewFile(files[0]);
            } else {
                showMessage(validation.errors.join(', '), 'error');
            }
        }
    }
}

// Initialize advanced features
document.addEventListener('DOMContentLoaded', function() {
    setupAdvancedDragDrop();
    
    // Add CSS for drag highlight
    const style = document.createElement('style');
    style.textContent = `
        .drag-highlight {
            border-color: #4caf50 !important;
            background: rgba(76, 175, 80, 0.2) !important;
            transform: scale(1.02);
        }
        .file-preview {
            margin-top: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .file-preview h4 {
            color: #64b5f6;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        .file-preview img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
    `;
    document.head.appendChild(style);
});

// Copy file URL to clipboard functionality
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showMessage('File URL copied to clipboard!', 'success');
        }).catch(() => {
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.top = '0';
    textArea.style.left = '0';
    textArea.style.position = 'fixed';
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showMessage('File URL copied to clipboard!', 'success');
    } catch (err) {
        showMessage('Failed to copy URL to clipboard', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Add copy buttons to file links
function addCopyButtons() {
    const fileLinks = document.querySelectorAll('.file-name');
    fileLinks.forEach(link => {
        if (!link.nextElementSibling || !link.nextElementSibling.classList.contains('copy-btn')) {
            const copyBtn = document.createElement('button');
            copyBtn.className = 'copy-btn';
            copyBtn.innerHTML = '📋';
            copyBtn.title = 'Copy file URL';
            copyBtn.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                copyToClipboard(window.location.origin + '/' + link.href.split('/').slice(-2).join('/'));
            };
            
            link.parentNode.appendChild(copyBtn);
        }
    });
    
    // Add styles for copy buttons
    if (!document.getElementById('copy-btn-styles')) {
        const style = document.createElement('style');
        style.id = 'copy-btn-styles';
        style.textContent = `
            .copy-btn {
                background: rgba(100, 181, 246, 0.2);
                border: 1px solid rgba(100, 181, 246, 0.3);
                border-radius: 4px;
                color: #64b5f6;
                cursor: pointer;
                font-size: 0.8rem;
                margin-left: 8px;
                padding: 2px 6px;
                transition: all 0.3s ease;
            }
            .copy-btn:hover {
                background: rgba(100, 181, 246, 0.3);
                transform: translateY(-1px);
            }
        `;
        document.head.appendChild(style);
    }
}

// Initialize copy buttons after DOM load
setTimeout(addCopyButtons, 100);

// Search functionality for files
function addSearchFunctionality() {
    const filesSection = document.querySelector('.files-section');
    if (!filesSection || document.getElementById('file-search')) return;
    
    const searchContainer = document.createElement('div');
    searchContainer.innerHTML = `
        <div style="margin-bottom: 20px;">
            <input type="text" id="file-search" placeholder="Search files..." 
                   style="width: 100%; padding: 10px; border: 1px solid rgba(255,255,255,0.3); 
                          border-radius: 8px; background: rgba(255,255,255,0.1); 
                          color: #e0e0e0; font-size: 14px;">
        </div>
    `;
    
    const h2 = filesSection.querySelector('h2');
    h2.parentNode.insertBefore(searchContainer, h2.nextSibling);
    
    const searchInput = document.getElementById('file-search');
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const fileItems = document.querySelectorAll('.file-item');
        
        fileItems.forEach(item => {
            const fileName = item.querySelector('.file-name').textContent.toLowerCase();
            if (fileName.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });
}

// Initialize search functionality
setTimeout(addSearchFunctionality, 100);