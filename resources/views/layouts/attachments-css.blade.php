{{-- Include this in your main layout file to load attachment styles --}}
<link href="{{ asset('css/attachments.css') }}" rel="stylesheet">

{{-- Optional: Add Font Awesome if not already included --}}
@if (!isset($fontAwesomeLoaded))
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endif

<style>
/* Additional inline styles for immediate visual feedback */
.file-upload-area {
    min-height: 120px;
}

.upload-placeholder {
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Ensure proper stacking for drag and drop */
.file-upload-area .form-control[type="file"] {
    z-index: 10;
}

/* Loading animation for file processing */
.attachment-processing {
    position: relative;
    opacity: 0.7;
}

.attachment-processing::after {
    content: '';
    position: absolute;
    top: 50%;
    right: 10px;
    width: 16px;
    height: 16px;
    margin-top: -8px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* File type badge styling */
.file-type-badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-left: 8px;
}

.file-type-badge.badge-word { background-color: #2b579a20; color: #2b579a; }
.file-type-badge.badge-excel { background-color: #21734620; color: #217346; }
.file-type-badge.badge-powerpoint { background-color: #d2472620; color: #d24726; }
.file-type-badge.badge-pdf { background-color: #dc354520; color: #dc3545; }
.file-type-badge.badge-image { background-color: #6f42c120; color: #6f42c1; }
.file-type-badge.badge-archive { background-color: #fd7e1420; color: #fd7e14; }
</style>
