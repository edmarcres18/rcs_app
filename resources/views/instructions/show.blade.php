@extends('layouts.app')

@section('title', 'Instruction Memo: ' . $instruction->title)

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Tinos:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    /* ---- Base Styles for the page ---- */
    .page-content {
        background-color: #e9e9e9; /* Light grey background to emphasize the paper */
    }

    /* ---- Memo Paper Styles ---- */
    .memo-paper {
        font-family: 'Tinos', 'Times New Roman', Times, serif;
        background: #fff;
        margin: 20px auto;
        padding: 40px;
        box-shadow: 0 0 15px rgba(0,0,0,0.15);

        /* Standard Letter paper size (8.5in x 11in) with aspect ratio */
        width: 100%;
        max-width: 816px; /* 8.5in * 96dpi */
        min-height: 1056px; /* 11in * 96dpi */
    }

    .memo-header, .memo-body, .memo-footer {
        width: 100%;
        max-width: 670px; /* Content width inside the paper */
        margin: 0 auto;
    }

    /* ---- Memo Header Section ---- */
    .memo-header {
        padding-bottom: 15px;
        margin-bottom: 25px;
    }
    .memo-header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .memo-logo {
        flex-shrink: 0;
    }
    .memo-logo img {
        max-height: 80px;
    }
    .memo-header-fields {
        flex-grow: 1;
        font-size: 14px;
    }
    .memo-header-fields table {
        width: 100%;
        border-collapse: collapse;
    }
    .memo-header-fields td {
        padding: 4px 0;
    }
    .memo-header-fields .field-label {
        font-weight: bold;
        width: 120px; /* Increased to fit "TARGET DEADLINE" */
        vertical-align: top;
    }
    .memo-header-fields .field-separator {
        width: 15px;
        text-align: center;
        vertical-align: top;
    }
    .memo-header-fields .field-value {
        text-align: left;
    }
    .header-divider {
        border: 0;
        border-top: 3px double #000;
        margin-top: 20px;
        opacity: 1;
    }

    /* ---- Memo Body Section ---- */
    .memo-body h3 {
        font-weight: bold;
        margin-bottom: 15px;
        font-size: 18px;
    }
    .memo-body p {
        text-align: justify;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }
    .memo-body ul {
        padding-left: 30px;
        list-style-type: disc;
    }
     .memo-body li {
        text-align: justify;
        margin-bottom: 0.5rem;
    }

    /* ---- Memo Signature & Footer ---- */
    .memo-signature {
        margin-top: 50px;
    }
    .signature-block {
        display: inline-block; /* Makes the container width fit the content */
        text-align: center;      /* Centers the role text beneath the line */
    }
    .signature-block p {
        margin-bottom: 0; /* Removes default paragraph margin */
    }
    .signature-line {
        margin: 8px 0;
        border: 0;
        border-top: 1px solid #333;
        opacity: 0.9;
        /* Width is now 100% of the dynamic parent container */
    }
    .memo-footer {
        margin-top: 100px;
        text-align: right;
        font-size: 12px;
        color: #888;
    }

    /* ---- Actions & Timeline Sidebar ---- */
    .actions-sidebar {
        position: sticky;
        top: 80px; /* Adjust based on navbar height */
    }

    /* ---- Responsive Design ---- */
    @media (max-width: 991px) {
        .memo-paper {
            padding: 20px;
            min-height: auto;
        }
        .actions-sidebar {
            position: static;
            top: auto;
            margin-top: 2rem;
        }
    }

    /* ---- Print-Ready Styles ---- */
    @media print {
        /* Hide app shell */
        body, .page-content {
            background: #fff !important;
            margin: 0;
            padding: 0;
        }
        .main-navbar, .sidebar, .sidebar-backdrop, .actions-sidebar, .breadcrumb {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .memo-paper {
            width: 100%;
            max-width: 100%;
            min-height: auto;
            margin: 0;
            padding: 0;
            box-shadow: none;
            border: none;
        }
        .memo-body p, .memo-body li {
            color: #000;
        }
    }
</style>
@endpush

@section('content')
<main class="page-content">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('instructions.index') }}">Instructions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Memo View</li>
            </ol>
        </nav>

        <div class="row">
            {{-- Memo Column --}}
            <div class="col-lg-8">
                <div class="memo-paper">
                    {{-- Memo Header --}}
                    <header class="memo-header">
                        <div class="memo-header-content">
                            <div class="memo-header-fields">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="field-label">TO</td>
                                            <td class="field-separator">:</td>
                                            <td class="field-value">
                                                @foreach($instruction->recipients as $recipient)
                                                    {{ $recipient->full_name }}{{ !$loop->last ? ', ' : '' }}
                                                @endforeach
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="field-label">RE</td>
                                            <td class="field-separator">:</td>
                                            <td class="field-value">{{ $instruction->title }}</td>
                                        </tr>
                                        <tr>
                                            <td class="field-label">DATE</td>
                                            <td class="field-separator">:</td>
                                            <td class="field-value">{{ $instruction->created_at->format('d F Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="field-label">INS. NO.</td>
                                            <td class="field-separator">:</td>
                                            <td class="field-value">INS-{{ $instruction->created_at->format('my') }}-{{ str_pad($instruction->id, 2, '0', STR_PAD_LEFT) }}</td>
                                        </tr>
                                        @if ($instruction->target_deadline)
                                        <tr>
                                            <td class="field-label">DEADLINE</td>
                                            <td class="field-separator">:</td>
                                            <td class="field-value" style="color: #D32F2F; font-weight: bold;">{{ \Carbon\Carbon::parse($instruction->target_deadline)->format('d F Y') }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="memo-logo">
                                <img src="{{ versioned_asset('images/instructions_logo/inslogo.png') }}" alt="Company Logo">
                            </div>
                        </div>
                        <hr class="header-divider">
                    </header>

                    {{-- Memo Body --}}
                    <section class="memo-body">
                        {!! nl2br(e($instruction->body)) !!}
                    </section>

                    {{-- Memo Signature --}}
                    <section class="memo-body">
                        <div class="memo-signature">
                            <div class="signature-block">
                                <p>{{ $instruction->sender->full_name }}</p>
                                <hr class="signature-line">
                                <p><em>{{ $instruction->sender->roles ?? 'Staff' }}</em></p>
                            </div>
                        </div>
                    </section>

                     {{-- Memo Footer --}}
                    <footer class="memo-footer">
                        Page 1 of 1
                    </footer>
                </div>
            </div>

            {{-- Actions and Timeline Column --}}
            <div class="col-lg-4">
                <div class="actions-sidebar">
                    {{-- Action Buttons --}}
                    <div class="card mb-4">
                        <div class="card-body text-center">
                             <button class="btn btn-outline-secondary" onclick="window.print();">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                            <a href="{{ route('instructions.show-forward', $instruction) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-share me-1"></i> Forward
                            </a>
                            <button class="btn btn-primary" onclick="document.getElementById('reply-content').focus()">
                                <i class="fas fa-reply me-1"></i> Reply
                            </button>
                        </div>
                    </div>

                    {{-- Reply Form --}}
                    <div class="card mb-4" id="reply-form-container">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Your Reply</h5>
                        </div>
                        <div class="card-body">
                            <form id="reply-form" action="{{ route('instructions.reply', $instruction) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="content" id="reply-content" class="form-control" rows="4" placeholder="Type your reply here..." required></textarea>
                                    <div id="content-error" class="invalid-feedback d-none"></div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane me-1"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Reply Success Alert Template (hidden) --}}
                    <div class="reply-success-alert d-none alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <span class="alert-message">Reply sent successfully!</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    {{-- Activity Feed --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Activity Feed</h5>
                        </div>
                        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                            <div class="timeline" id="timeline-container">
                                @php
                                    $activities = $activities->where('action', '!=', 'replied');
                                    $allItems = $activities->concat($replies)->sortBy('created_at');
                                @endphp

                                @forelse($allItems as $item)
                                    @if($item instanceof \App\Models\InstructionReply)
                                        @include('instructions.partials._timeline_reply_item', ['reply' => $item])
                                    @else
                                        @include('instructions.partials._timeline_activity_item', ['activity' => $item])
                                    @endif
                                @empty
                                    <div class="text-center text-muted p-4">No activity yet.</div>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reply-form');
    if (!form) return;

    const contentField = document.getElementById('reply-content');
    const contentError = document.getElementById('content-error');
    const timelineContainer = document.getElementById('timeline-container');

    // Reset validation states
    function resetValidationState() {
        contentField.classList.remove('is-invalid');
        contentError.classList.add('d-none');
    }

    // Form submission handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        resetValidationState();

        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonHtml = submitButton.innerHTML;

        // Disable button and show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...`;

        // Check if the content is empty (additional client-side validation)
        if (!formData.get('content').trim()) {
            contentField.classList.add('is-invalid');
            contentError.textContent = 'Please enter a reply.';
            contentError.classList.remove('d-none');
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonHtml;
            return;
        }

        // Keep track if we've shown a success message
        let successfulSubmission = false;

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest' // Explicitly mark as AJAX request
            }
        })
        .then(response => {
            if (!response.ok) {
                // Only try to parse as JSON if we get a JSON response
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(err => {
                        throw { status: response.status, data: err };
                    });
                } else {
                    throw { status: response.status, message: 'Server error: ' + response.statusText };
                }
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                successfulSubmission = true;

                // Show success toast notification that automatically disappears
                Swal.fire({
                    icon: 'success',
                    title: 'Reply Sent Successfully!',
                    text: 'Page will refresh in a moment...',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });

                // Try to add the new reply to the timeline without page refresh
                try {
                    const newReplyHtml = createReplyHtml(data.reply);
                    const placeholder = timelineContainer.querySelector('.text-center.text-muted');
                    if (placeholder) placeholder.remove();

                    // Insert new reply with animation
                    const newReplyElement = document.createElement('div');
                    newReplyElement.innerHTML = newReplyHtml;
                    newReplyElement.firstChild.classList.add('animate__animated', 'animate__fadeIn');
                    timelineContainer.appendChild(newReplyElement.firstChild);

                    // Scroll timeline to bottom to show the new reply
                    timelineContainer.scrollTop = timelineContainer.scrollHeight;
                } catch (err) {
                    console.log('Could not render reply in timeline:', err);
                }

                // Reset form
                form.reset();
                fileInfo.innerHTML = '';

                // Automatically refresh the page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error details:', error);

            // If we already know the submission was successful, don't show an error
            if (successfulSubmission) {
                console.log('Ignoring error as submission was successful');
                return;
            }

            // Check if this is actually a 200 response with success status
            // This handles cases where the fetch succeeds but JSON parsing fails
            if (error.data && error.data.success === true) {
                console.log('Response indicates success despite error in processing');

                // Show simple toast and auto-refresh
                Swal.fire({
                    icon: 'success',
                    title: 'Reply Sent',
                    text: 'Refreshing page...',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });

                setTimeout(() => {
                    window.location.reload();
                }, 2000);

                return;
            }

            let errorMessage = 'Failed to send reply. Please try again.';

            // Handle different error scenarios
            if (error.data && error.data.message) {
                errorMessage = error.data.message;
            } else if (error.data && error.data.errors) {
                // Display field-specific validation errors
                const errors = error.data.errors;

                if (errors.content) {
                    contentField.classList.add('is-invalid');
                    contentError.textContent = errors.content[0];
                    contentError.classList.remove('d-none');
                }

                errorMessage = Object.values(errors)[0][0];
            }

            // Show toast notification
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        })
        .finally(() => {
            // Re-enable button regardless of outcome
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonHtml;
        });
    });

    function createReplyHtml(reply) {
        // Format the timestamp using moment.js
        const formattedTime = moment(reply.created_at).format('MMM D, YYYY, h:mm A');
        const relativeTime = moment(reply.created_at).fromNow();

        // Handle attachment display if present
        let attachmentHtml = '';
        if (reply.attachment) {
            const fileName = reply.attachment.split('/').pop();

            // Determine icon based on file extension
            let fileIcon = 'fa-paperclip';
            const fileExt = fileName.split('.').pop().toLowerCase();

            if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                fileIcon = 'fa-file-image';
            } else if (fileExt === 'pdf') {
                fileIcon = 'fa-file-pdf';
            } else if (['doc', 'docx'].includes(fileExt)) {
                fileIcon = 'fa-file-word';
            } else if (['xls', 'xlsx'].includes(fileExt)) {
                fileIcon = 'fa-file-excel';
            }

            attachmentHtml = `
                <hr>
                <div class="mt-2">
                    <a href="${reply.attachment_url}" target="_blank" class="attachment-link text-decoration-none" download>
                        <i class="fas ${fileIcon} me-2 text-muted"></i>
                        <span class="text-dark">${fileName}</span>
                    </a>
                </div>
            `;
        }

        // Use the avatar URL if provided, otherwise use a default
        const avatarUrl = reply.user.avatar_url || '/images/default-avatar.png';

        return `
        <div class="timeline-item">
            <div class="timeline-icon" title="Reply">
                <i class="fas fa-reply"></i>
            </div>
            <div class="timeline-content">
                <div class="d-flex align-items-center mb-2">
                    <img src="${avatarUrl}" alt="${reply.user.name}" class="rounded-circle" width="32" height="32">
                    <span class="fw-bold ms-2">${reply.user.name}</span>
                    <small class="text-muted ms-auto" title="${formattedTime}">${relativeTime}</small>
                </div>
                <div class="card reply-card">
                    <div class="card-body p-3">
                        <p class="mb-0" style="white-space: pre-wrap;">${reply.content}</p>
                        ${attachmentHtml}
                    </div>
                </div>
            </div>
        </div>`;
    }
});
</script>
@endpush
