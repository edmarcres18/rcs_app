@csrf
<div class="row">
    <div class="col-12">
        <div class="mb-3">
            <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $notification->title ?? '') }}" required maxlength="255" placeholder="Enter notification title">
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-12">
        <div class="mb-3">
            <label for="message" class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
            <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="4" required placeholder="Enter notification message">{{ old('message', $notification->message ?? '') }}</textarea>
            @error('message')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Provide a clear and concise message for users.</div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="mb-3">
            <label for="type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                <option value="">Select Type</option>
                <option value="info" {{ old('type', $notification->type ?? '') == 'info' ? 'selected' : '' }}>
                    <i class="fas fa-info-circle"></i> Info
                </option>
                <option value="update" {{ old('type', $notification->type ?? '') == 'update' ? 'selected' : '' }}>
                    <i class="fas fa-sync-alt"></i> Update
                </option>
                <option value="maintenance" {{ old('type', $notification->type ?? '') == 'maintenance' ? 'selected' : '' }}>
                    <i class="fas fa-tools"></i> Maintenance
                </option>
                <option value="alert" {{ old('type', $notification->type ?? '') == 'alert' ? 'selected' : '' }}>
                    <i class="fas fa-exclamation-triangle"></i> Alert
                </option>
            </select>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="mb-3">
            <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="active" {{ old('status', $notification->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $notification->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="archived" {{ old('status', $notification->status ?? '') == 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="mb-3">
            <label for="date_start" class="form-label fw-semibold">Start Date</label>
            <input type="datetime-local" class="form-control @error('date_start') is-invalid @enderror" id="date_start" name="date_start" value="{{ old('date_start', isset($notification->date_start) ? $notification->date_start->format('Y-m-d\TH:i') : '') }}">
            @error('date_start')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">When notification becomes visible</div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="mb-3">
            <label for="date_end" class="form-label fw-semibold">End Date</label>
            <input type="datetime-local" class="form-control @error('date_end') is-invalid @enderror" id="date_end" name="date_end" value="{{ old('date_end', isset($notification->date_end) ? $notification->date_end->format('Y-m-d\TH:i') : '') }}">
            @error('date_end')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">When notification expires</div>
        </div>
    </div>
</div>

<hr class="my-4">

<div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
    <a href="{{ route('admin.system-notifications.index') }}" class="btn btn-outline-secondary order-2 order-sm-1">
        <i class="fas fa-arrow-left me-1"></i> Cancel
    </a>
    <button type="submit" class="btn btn-primary order-1 order-sm-2" id="submitBtn">
        <span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner" role="status" aria-hidden="true"></span>
        <i class="fas fa-save me-1" id="submitIcon"></i>
        <span id="submitText">{{ $submitButtonText ?? 'Create Notification' }}</span>
    </button>
</div>
