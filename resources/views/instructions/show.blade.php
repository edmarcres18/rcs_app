@extends('layouts.app')

@section('title', 'Instruction: ' . $instruction->title)

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    :root {
        --primary-color: #4f46e5;
        --primary-color-rgb: 79, 70, 229;
        --text-color: #111827;
        --text-muted: #6b7280;
        --border-color: #e5e7eb;
        --bg-card: #ffffff;
        --bg-light: #f9fafb;
        --bg-reply: #f3f4f6;
    }

    .page-content {
        background-color: var(--bg-light);
    }

    /* --- Header & Actions --- */
    .page-header {
        background-color: var(--bg-card);
        padding: 1.5rem 0;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 2rem;
    }
    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-color);
        margin-bottom: 0.25rem;
    }
    .page-subtitle {
        font-size: 0.95rem;
        color: var(--text-muted);
    }
    .action-buttons .btn {
        border-radius: 0.5rem;
    }

    /* --- Instruction Content Card --- */
    .instruction-card {
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .instruction-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-color);
    }
    .instruction-meta-item {
        display: flex;
        align-items: center;
        color: var(--text-muted);
    }
    .instruction-meta-item i {
        width: 20px;
        text-align: center;
        margin-right: 0.75rem;
        color: #9ca3af;
    }
    .instruction-body {
        padding: 2rem 1.5rem;
        line-height: 1.7;
        color: #374151;
    }
    .instruction-body p:last-child {
        margin-bottom: 0;
    }
    .instruction-signature {
        padding: 1.5rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
    }
    .signature-avatar img {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
    }
    .signature-name {
        font-weight: 600;
        color: var(--text-color);
    }
    .signature-role {
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    /* Avatar styles for timeline items */
    .timeline-content img.rounded-circle {
        background-color: #fff;
        flex-shrink: 0;
    }

    /* --- Reply Form & Activity Timeline --- */
    .timeline-container {
        position: sticky;
        top: 20px;
    }
    .card-timeline .card-header, .card-reply .card-header {
        background-color: transparent;
        border-bottom: 1px solid var(--border-color);
    }
    .card-title-small {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-color);
    }
    #reply-form textarea {
        border-radius: 0.5rem;
        min-height: 120px;
    }
    .activity-feed {
        max-height: 600px;
        overflow-y: auto;
        padding: 0.5rem;
    }

    .timeline-item {
        position: relative;
    }
    .timeline-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.9rem;
    }
    .bg-primary-soft { background-color: rgba(79, 70, 229, 0.1); }
    .text-primary { color: #4f46e5 !important; }
    .bg-info-soft { background-color: rgba(59, 130, 246, 0.1); }
    .text-info { color: #3b82f6 !important; }
    .bg-success-soft { background-color: rgba(22, 163, 74, 0.1); }
    .text-success { color: #16a34a !important; }
    .bg-secondary-soft { background-color: #f3f4f6; }
    .text-secondary { color: #6b7280 !important; }

    .reply-bubble {
        background-color: var(--bg-reply);
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
    }
    .reply-attachment {
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
    }
    .attachment-link {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
        color: var(--text-muted);
        text-decoration: none;
        transition: color 0.2s;
    }
    .attachment-link:hover {
        color: var(--primary-color);
    }

    /* --- Responsive --- */
    @media (max-width: 991px) {
        .timeline-container {
            position: static;
            margin-top: 2rem;
        }
    }
    @media (max-width: 767px) {
        .page-header {
            text-align: center;
        }
        .action-buttons {
            margin-top: 1rem;
        }
        .instruction-card {
            border-radius: 0;
            border-left: 0;
            border-right: 0;
        }
    }

    /* --- Print Styles --- */
    @media print {
        body, .page-content {
            background: #fff !important;
            margin: 0;
            padding: 0;
        }
        .main-navbar, .sidebar, .sidebar-backdrop, .page-header, .timeline-container {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .instruction-card {
            box-shadow: none;
            border: none;
        }
    }
</style>
@endpush

@section('content')
<main class="page-content">
    {{-- Header --}}
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7">
                    <h1 class="page-title">{{ $instruction->title }}</h1>
                    <p class="page-subtitle">
                        Instruction from {{ $instruction->sender->full_name }} sent on {{ $instruction->created_at->format('M d, Y') }}
                    </p>
                </div>
                <div class="col-lg-4 col-md-5">
                    <div class="action-buttons d-flex justify-content-md-end gap-2">
                         <button class="btn btn-outline-secondary" onclick="window.print();">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                        <a href="{{ route('instructions.show-forward', $instruction) }}" class="btn btn-outline-primary">
                            <i class="fas fa-share me-1"></i> Forward
                        </a>
                        <button class="btn btn-primary" onclick="document.getElementById('reply-content').focus()">
                            <i class="fas fa-reply me-1"></i> Reply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row g-4">
            {{-- Instruction Content Column --}}
            <div class="col-lg-7">
                <div class="card instruction-card">
                    <div class="instruction-header">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="instruction-meta-item">
                                    <i class="fas fa-paper-plane"></i>
                                    <div>
                                        <strong>From:</strong> {{ $instruction->sender->full_name }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="instruction-meta-item">
                                    <i class="fas fa-users"></i>
                                    <div>
                                        <strong>To:</strong> {{ $recipientDisplay }}
                                    </div>
                                </div>
                            </div>
                             @if ($instruction->target_deadline)
                            <div class="col-12">
                                <div class="instruction-meta-item text-danger">
                                    <i class="fas fa-calendar-alt"></i>
                                    <div>
                                        <strong>Deadline:</strong> {{ \Carbon\Carbon::parse($instruction->target_deadline)->format('d F Y, h:i A') }}
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="instruction-body">
                        {!! nl2br(e($instruction->body)) !!}
                    </div>

                    <div class="instruction-signature">
                        <div class="signature-avatar me-3">
                            <img src="{{ $instruction->sender->avatar }}" 
                                 alt="{{ $instruction->sender->full_name }}"
                                 class="border border-2 border-light shadow-sm"
                                 style="object-fit: cover;"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($instruction->sender->full_name) }}&color=7F9CF5&background=EBF4FF';">
                        </div>
                        <div>
                            <div class="signature-name">{{ $instruction->sender->full_name }}</div>
                            <div class="signature-role">{{ $instruction->sender->roles ?? 'Staff' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions and Timeline Column --}}
            <div class="col-lg-5">
                <div class="timeline-container">
                    {{-- Reply Form --}}
                    <div class="card card-reply mb-4" id="reply-form-container">
                        <div class="card-header py-3">
                            <h5 class="card-title-small mb-0"><i class="fas fa-pen-alt me-2"></i>Your Reply</h5>
                        </div>
                        <div class="card-body">
                            <form id="reply-form" action="{{ route('instructions.reply', $instruction) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="content" id="reply-content" class="form-control" placeholder="Type your reply here..." required></textarea>
                                    <div id="content-error" class="invalid-feedback d-none"></div>
                                </div>

                                {{-- File Upload Section --}}
                                <div class="mb-3">
                                    <label for="attachment" class="form-label">
                                        <i class="fas fa-paperclip me-1"></i> Attach File (Optional)
                                    </label>

                                    {{-- Enhanced File Upload Area --}}
                                    <div class="file-upload-area" id="file-upload-area">
                                        <input type="file" name="attachment" id="attachment" class="form-control"
                                               accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.tiff,.ico,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.rtf,.odt,.ods,.odp,.zip,.rar,.7z,.csv,.json,.xml">
                                        <div class="upload-placeholder" id="upload-placeholder">
                                            <div class="text-center p-4">
                                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                                <p class="mb-2"><strong>Click to browse</strong> or drag and drop your file here</p>
                                                <small class="text-muted">
                                                    Maximum file size: 25MB<br>
                                                    Supported: Images, PDF, Word, Excel, PowerPoint, Archives, Text files
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="attachment-error" class="invalid-feedback d-none"></div>

                                    {{-- File Preview --}}
                                    <div id="file-preview" class="mt-3 d-none">
                                        <div class="alert alert-info d-flex align-items-center">
                                            <i id="file-icon" class="fas fa-file me-3"></i>
                                            <div class="flex-grow-1">
                                                <div id="file-name" class="file-name"></div>
                                                <div id="file-size" class="file-size"></div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="remove-file" title="Remove file">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane me-1"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Activity Feed --}}
                    <div class="card card-timeline">
                        <div class="card-header py-3">
                            <h5 class="card-title-small mb-0"><i class="fas fa-stream me-2"></i>Activity Feed</h5>
                        </div>
                        <div class="card-body activity-feed">
                            <div class="timeline" id="timeline-container">
                                @php
                                    $activities = $activities->where('action', '!=', 'replied');
                                    $allItems = $activities->concat($replies)->sortByDesc('created_at');
                                @endphp

                                @forelse($allItems as $item)
                                    @if($item instanceof \App\Models\InstructionReply)
                                        @include('instructions.partials._timeline_reply_item', ['reply' => $item])
                                    @else
                                        @include('instructions.partials._timeline_activity_item', ['activity' => $item])
                                    @endif
                                @empty
                                    <div class="text-center text-muted p-4">
                                        <i class="fas fa-history fa-2x mb-2"></i>
                                        <p>No activity yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection


@push('scripts')
{{-- The script remains largely the same as it handles the logic, not the UI. --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reply-form');
    if (!form) return;

    function escapeHTML(str) {
        return (str || '').replace(/[&<>"']/g, function(m) {
            switch (m) {
                case '&': return '&amp;';
                case '<': return '&lt;';
                case '>': return '&gt;';
                case '"': return '&quot;';
                case "'": return '&#39;';
                default: return m;
            }
        });
    }

    function linkifyText(text) {
        const escaped = escapeHTML(text);
        const urlRegex = /(?:(https?:\/\/[^\s<]+)|(?:www\.[^\s<]+?\.[^\s<]+))/gi;
        return escaped.replace(urlRegex, function(match) {
            let url = match;
            let trailing = '';
            while (/[\.,!?;:\)\]\}]$/.test(url)) { trailing = url.slice(-1) + trailing; url = url.slice(0, -1); }
            let href = url;
            if (!/^https?:\/\//i.test(href)) href = 'http://' + href;
            return `<a href="${href}" target="_blank" rel="noopener noreferrer">${url}</a>${trailing}`;
        }).replace(/\n/g, '<br>');
    }

    function linkifyElement(el) {
        if (!el) return;
        el.innerHTML = linkifyText(el.textContent || '');
    }

    const contentField = document.getElementById('reply-content');
    const contentError = document.getElementById('content-error');
    const timelineContainer = document.getElementById('timeline-container');
    const attachmentField = document.getElementById('attachment');
    const attachmentError = document.getElementById('attachment-error');
    const filePreview = document.getElementById('file-preview');
    const fileIcon = document.getElementById('file-icon');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const removeFileBtn = document.getElementById('remove-file');

    // Enhanced file type icons and categories mapping
    const fileIcons = {
        // Microsoft Office - Brand colors
        'doc': 'fas fa-file-word text-word',
        'docx': 'fas fa-file-word text-word',
        'xls': 'fas fa-file-excel text-excel',
        'xlsx': 'fas fa-file-excel text-excel',
        'ppt': 'fas fa-file-powerpoint text-powerpoint',
        'pptx': 'fas fa-file-powerpoint text-powerpoint',

        // PDF
        'pdf': 'fas fa-file-pdf text-pdf',

        // Images
        'jpg': 'fas fa-image text-image',
        'jpeg': 'fas fa-image text-image',
        'png': 'fas fa-image text-image',
        'gif': 'fas fa-image text-image',
        'bmp': 'fas fa-image text-image',
        'webp': 'fas fa-image text-image',
        'svg': 'fas fa-image text-image',
        'tiff': 'fas fa-image text-image',
        'ico': 'fas fa-image text-image',

        // Text files
        'txt': 'fas fa-file-alt text-text',
        'rtf': 'fas fa-file-alt text-text',
        'csv': 'fas fa-file-csv text-excel',
        'json': 'fas fa-file-code text-code',
        'xml': 'fas fa-file-code text-code',

        // Archives
        'zip': 'fas fa-file-archive text-archive',
        'rar': 'fas fa-file-archive text-archive',
        '7z': 'fas fa-file-archive text-archive',

        // OpenDocument
        'odt': 'fas fa-file-word text-word',
        'ods': 'fas fa-file-excel text-excel',
        'odp': 'fas fa-file-powerpoint text-powerpoint'
    };

    const fileCategories = {
        'doc': 'word', 'docx': 'word', 'odt': 'word',
        'xls': 'excel', 'xlsx': 'excel', 'ods': 'excel', 'csv': 'excel',
        'ppt': 'powerpoint', 'pptx': 'powerpoint', 'odp': 'powerpoint',
        'pdf': 'pdf',
        'jpg': 'image', 'jpeg': 'image', 'png': 'image', 'gif': 'image',
        'bmp': 'image', 'webp': 'image', 'svg': 'image', 'tiff': 'image', 'ico': 'image',
        'zip': 'archive', 'rar': 'archive', '7z': 'archive',
        'txt': 'text', 'rtf': 'text',
        'json': 'code', 'xml': 'code'
    };

    function resetValidationState() {
        contentField.classList.remove('is-invalid');
        contentError.classList.add('d-none');
        attachmentField.classList.remove('is-invalid');
        attachmentError.classList.add('d-none');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function getFileIcon(extension) {
        return fileIcons[extension.toLowerCase()] || 'fas fa-file text-secondary';
    }

    function getFileCategory(extension) {
        return fileCategories[extension.toLowerCase()] || 'document';
    }

    // Handle file selection
    attachmentField.addEventListener('change', function(e) {
        const file = e.target.files[0];

        if (file) {
            // Validate file size (25MB = 26214400 bytes)
            if (file.size > 26214400) {
                attachmentField.classList.add('is-invalid');
                attachmentError.textContent = 'File size must be less than 25MB.';
                attachmentError.classList.remove('d-none');
                attachmentField.value = '';
                filePreview.classList.add('d-none');
                return;
            }

            // Show file preview with enhanced styling
            const extension = file.name.split('.').pop();
            const category = getFileCategory(extension);

            fileIcon.className = getFileIcon(extension);
            fileName.textContent = file.name;
            fileName.className = 'file-name';
            fileSize.textContent = formatFileSize(file.size);
            fileSize.className = 'file-size';

            // Add file category class to preview
            const alertElement = filePreview.querySelector('.alert');
            alertElement.className = `alert alert-info d-flex align-items-center file-${category}`;

            filePreview.classList.remove('d-none');

            // Add animation
            setTimeout(() => {
                filePreview.classList.add('new-attachment');
            }, 10);

            // Clear any previous errors
            attachmentField.classList.remove('is-invalid');
            attachmentError.classList.add('d-none');
        } else {
            filePreview.classList.add('d-none');
        }
    });

    // Handle file removal
    removeFileBtn.addEventListener('click', function() {
        attachmentField.value = '';
        filePreview.classList.add('d-none');
        filePreview.classList.remove('new-attachment');
        attachmentField.classList.remove('is-invalid');
        attachmentError.classList.add('d-none');

        // Show upload placeholder again
        const uploadPlaceholder = document.getElementById('upload-placeholder');
        uploadPlaceholder.style.display = 'block';
    });

    // Enhanced drag and drop functionality
    const fileUploadArea = document.getElementById('file-upload-area');
    const uploadPlaceholder = document.getElementById('upload-placeholder');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, unhighlight, false);
    });

    // Handle dropped files
    fileUploadArea.addEventListener('drop', handleDrop, false);

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight(e) {
        uploadPlaceholder.classList.add('drag-over');
    }

    function unhighlight(e) {
        uploadPlaceholder.classList.remove('drag-over');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            attachmentField.files = files;
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            attachmentField.dispatchEvent(event);
        }
    }

    // Hide upload placeholder when file is selected
    attachmentField.addEventListener('change', function() {
        if (this.files.length > 0) {
            uploadPlaceholder.style.display = 'none';
        } else {
            uploadPlaceholder.style.display = 'block';
        }
    });

    linkifyElement(document.querySelector('.instruction-body'));
    document.querySelectorAll('.reply-bubble p').forEach(linkifyElement);

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        resetValidationState();

        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonHtml = submitButton.innerHTML;

        submitButton.disabled = true;
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...`;

        if (!formData.get('content').trim()) {
            contentField.classList.add('is-invalid');
            contentError.textContent = 'Please enter a reply.';
            contentError.classList.remove('d-none');
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonHtml;
            return;
        }

        let successfulSubmission = false;

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(err => { throw { status: response.status, data: err }; });
                } else {
                    throw { status: response.status, message: 'Server error: ' + response.statusText };
                }
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                successfulSubmission = true;
                Swal.fire({
                    icon: 'success',
                    title: 'Reply Sent!',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });

                const newReplyHtml = createReplyHtml(data.reply);
                const placeholder = timelineContainer.querySelector('.text-center.text-muted');
                if (placeholder) placeholder.remove();

                const newReplyElement = document.createElement('div');
                newReplyElement.innerHTML = newReplyHtml;
                const insertedElement = timelineContainer.insertBefore(newReplyElement.firstElementChild, timelineContainer.firstChild);
                insertedElement.classList.add('animate__animated', 'animate__fadeInDown');

                const replyTextEl = insertedElement.querySelector('.reply-text');
                if (replyTextEl) {
                    replyTextEl.innerHTML = linkifyText(reply.content || '');
                }

                timelineContainer.scrollTop = 0;
                form.reset();
                filePreview.classList.add('d-none');
            }
        })
        .catch(error => {
            if (successfulSubmission) return;
            let errorMessage = 'Failed to send reply. Please try again.';
            if (error.data && error.data.errors) {
                const errors = error.data.errors;
                if (errors.content) {
                    contentField.classList.add('is-invalid');
                    contentError.textContent = errors.content[0];
                    contentError.classList.remove('d-none');
                }
                if (errors.attachment) {
                    attachmentField.classList.add('is-invalid');
                    attachmentError.textContent = errors.attachment[0];
                    attachmentError.classList.remove('d-none');
                }
                errorMessage = Object.values(errors)[0][0];
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000
            });
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonHtml;
        });
    });

    function createReplyHtml(reply) {
        const formattedTime = moment(reply.created_at).format('MMM D, YYYY, h:mm A');
        const relativeTime = moment(reply.created_at).fromNow();
        const userName = reply.user.name || 'User';
        const avatarUrl = reply.user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&color=7F9CF5&background=EBF4FF`;

        let attachmentHtml = '';
        if (reply.attachment) {
            const downloadUrl = `/instructions/replies/${reply.id}/download`;
            const extension = reply.attachment.original_name.split('.').pop().toLowerCase();
            const category = getFileCategory(extension);

            attachmentHtml = `
                <div class="mt-3">
                    <div class="attachment-item file-${category} d-flex align-items-center p-3 new-attachment">
                        <i class="${reply.attachment.icon} me-3"></i>
                        <div class="flex-grow-1">
                            <a href="${downloadUrl}" class="file-name text-decoration-none" target="_blank">
                                ${reply.attachment.original_name}
                            </a>
                            <br>
                            <span class="file-size">${reply.attachment.size}</span>
                        </div>
                        <a href="${downloadUrl}" class="btn btn-sm btn-outline-primary" target="_blank" title="Download ${reply.attachment.original_name}">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>`;
        }

        return `
        <div class="timeline-item d-flex align-items-start mb-4">
            <div class="timeline-icon bg-primary-soft text-primary" title="Reply">
                <i class="fas fa-reply"></i>
            </div>
            <div class="timeline-content ps-3 flex-grow-1">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="${avatarUrl}" 
                             alt="${userName}" 
                             class="rounded-circle border border-2 border-light shadow-sm" 
                             width="36" 
                             height="36"
                             style="object-fit: cover;"
                             onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&color=7F9CF5&background=EBF4FF';">
                        <div class="ms-2">
                            <span class="fw-bold d-block small">${userName}</span>
                        </div>
                    </div>
                    <small class="text-muted" title="${formattedTime}">${relativeTime}</small>
                </div>
                <div class="reply-bubble mt-2">
                    <p class="mb-0 reply-text" style="white-space: pre-wrap;"></p>
                    ${attachmentHtml}
                </div>
            </div>
        </div>`;
    }
});
</script>
@endpush
