@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 d-flex align-items-center">
            <i class="fas fa-database text-primary mr-2"></i>&nbsp;Database Backups
        </h1>
        <div class="btn-toolbar">
            <div class="btn-group">
                <a href="{{ route('database.backup.create') }}" id="createBackupBtn" class="btn btn-primary shadow-sm">
                    <i class="fas fa-download fa-sm text-white-50 mr-1"></i>&nbsp;Create Backup
                </a>
            </div>
        </div>
    </div>
    <!-- Access Control Notice -->
    <div class="alert alert-info alert-dismissible fade show shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-shield-alt mr-2 fa-lg"></i>&nbsp;
            <strong>SYSTEM_ADMIN Access Only:</strong>&nbsp;Database backup functionality is restricted to SYSTEM_ADMIN users only.
        </div>
    </div>

    <!-- Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Backups</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBackupsCount">{{ count($backups) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Latest Backup</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="latestBackupTime">
                                @if(count($backups) > 0)
                                    {{ $backups[0]['age'] }}
                                @else
                                    None
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Storage Used</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalStorageUsed">
                                @php
                                    $totalSize = 0;
                                    foreach($backups as $backup) {
                                        $totalSize += strpos($backup['size'], 'KB') !== false ? 
                                            (float)str_replace(' KB', '', $backup['size']) * 1024 : 
                                            (strpos($backup['size'], 'MB') !== false ? 
                                                (float)str_replace(' MB', '', $backup['size']) * 1024 * 1024 : 
                                                (float)str_replace(' B', '', $backup['size']));
                                    }
                                    echo $totalSize > 0 ? \App\Http\Controllers\DatabaseBackupController::formatFileSizeStatic($totalSize) : '0 B';
                                @endphp
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Average Size</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="averageBackupSize">
                                @php
                                    $count = count($backups);
                                    echo $count > 0 ? \App\Http\Controllers\DatabaseBackupController::formatFileSizeStatic($totalSize / $count) : '0 B';
                                @endphp
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-archive fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Files Card -->
    <div class="card shadow mb-4 border-0">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-gradient-light">
            <h6 class="m-0 font-weight-bold text-primary">Available Backups</h6>
        </div>
        <div class="card-body">
            <div id="backupsTableContainer">
                @if(count($backups) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover shadow-sm" id="backupsTable" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>File Name</th>
                                    <th width="10%">Size</th>
                                    <th width="15%">Date</th>
                                    <th width="15%">Age</th>
                                    <th width="15%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($backups as $backup)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-circle text-primary mr-3">
                                                    <i class="fas fa-file-code"></i>&nbsp;
                                                </div>
                                                <div>
                                                    <span class="font-weight-medium text-truncate d-inline-block" style="max-width: 350px;" title="{{ $backup['filename'] }}">
                                                        {{ $backup['filename'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle font-weight-medium">{{ $backup['size'] }}</td>
                                        <td class="align-middle">{{ $backup['date'] }}</td>
                                        <td class="align-middle">
                                            @php
                                                $ageHours = (time() - strtotime($backup['date'])) / 3600;
                                                // Use bg-* utilities plus text-white for better compatibility and visibility
                                                $badgeClass = $ageHours < 24 
                                                    ? 'bg-success text-white' 
                                                    : ($ageHours < 168 
                                                        ? 'bg-info text-white' 
                                                        : 'bg-secondary text-white');
                                            @endphp
                                            <span class="badge {{ $badgeClass }} badge-pill">{{ $backup['age'] }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('database.backup.download', $backup['filename']) }}" 
                                                class="btn btn-sm btn-primary rounded-left download-btn" 
                                                data-toggle="tooltip" 
                                                data-placement="top"
                                                title="Download (SYSTEM_ADMIN Only)">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                &nbsp;
                                                <form action="{{ route('database.backup.delete', $backup['filename']) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-danger rounded-right"
                                                            data-toggle="tooltip"
                                                            data-placement="top"
                                                            title="Move to Trash (SYSTEM_ADMIN Only)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="empty-state-container">
                            <div class="empty-state-icon mb-3">
                                <i class="fas fa-database fa-5x text-gray-300"></i>
                            </div>
                            <h4 class="text-gray-600 mb-2 font-weight-bold">No Backups Available</h4>
                            <p class="text-gray-500 mb-4">Create your first database backup to protect your data.</p>
                            <a href="{{ route('database.backup.create') }}" class="btn btn-primary px-4 py-2 shadow-sm">
                                <i class="fas fa-download mr-2"></i> Create First Backup
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Trash (Soft-Deleted Backups) -->
    <div class="card shadow mb-4 border-0">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-gradient-light">
            <h6 class="m-0 font-weight-bold text-danger">
                <i class="fas fa-recycle mr-1"></i> Trash
                <span class="badge badge-pill badge-secondary ml-2">{{ isset($trashBackups) ? count($trashBackups) : 0 }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if(isset($trashBackups) && count($trashBackups) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover shadow-sm" id="trashTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th>File Name</th>
                                <th width="10%">Size</th>
                                <th width="15%">Date</th>
                                <th width="15%">Age</th>
                                <th width="20%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trashBackups as $backup)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle text-danger mr-3">
                                                <i class="fas fa-file-code"></i>&nbsp;
                                            </div>
                                            <div>
                                                <span class="font-weight-medium text-truncate d-inline-block" style="max-width: 350px;" title="{{ $backup['filename'] }}">
                                                    {{ $backup['filename'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle font-weight-medium">{{ $backup['size'] }}</td>
                                    <td class="align-middle">{{ $backup['date'] }}</td>
                                    <td class="align-middle">
                                        @php
                                            $ageHours = (time() - strtotime($backup['date'])) / 3600;
                                            $badgeClass = $ageHours < 24 
                                                ? 'bg-success text-white' 
                                                : ($ageHours < 168 
                                                    ? 'bg-info text-white' 
                                                    : 'bg-secondary text-white');
                                        @endphp
                                        <span class="badge {{ $badgeClass }} badge-pill">{{ $backup['age'] }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group" role="group">
                                            <form action="{{ route('database.backup.restore', $backup['filename']) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-success"
                                                        data-toggle="tooltip"
                                                        data-placement="top"
                                                        title="Restore from Trash (SYSTEM_ADMIN Only)">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                            &nbsp;
                                            <form action="{{ route('database.backup.destroy', $backup['filename']) }}" method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this backup? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        data-toggle="tooltip"
                                                        data-placement="top"
                                                        title="Permanently Delete (SYSTEM_ADMIN Only)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox mr-2"></i> Trash is empty.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="card p-4 shadow-lg border-0 rounded-lg" style="min-width: 300px;">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <h5 class="text-gray-800 font-weight-bold mb-2">Creating Backup</h5>
                <p class="text-muted mb-0">Please wait while we backup your database...</p>
                <div class="progress mt-4" style="height: 6px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Backup Modal -->
<div class="modal fade" id="deleteBackupModal" tabindex="-1" role="dialog" aria-labelledby="deleteBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-gradient-danger text-white">
                <h5 class="modal-title" id="deleteBackupModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirm Delete
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to move this backup to Trash? You can restore it later from Trash.</p>
                <div class="alert alert-warning d-flex align-items-center">
                    <i class="fas fa-file-code fa-lg mr-3"></i>
                    <span id="deleteFilename" class="font-weight-bold"></span>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <form id="deleteBackupForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger shadow-sm">
                        <i class="fas fa-trash mr-1"></i> Move to Trash
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="helpModalLabel">
                    <i class="fas fa-question-circle mr-2"></i>Database Backup Help
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-shield-alt mr-2 fa-lg"></i>
                        <strong>Access Restriction:</strong> Only users with SYSTEM_ADMIN role can access, create, download, or delete database backups.
                    </div>
                </div>
                
                <div class="card-deck mb-4">
                    <div class="card shadow-sm border-left-primary">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-download text-primary mr-2"></i>Creating Backups
                            </h5>
                            <p class="card-text">
                                Create a backup by clicking the "Create Backup" button. This will generate a SQL file containing your entire database structure and data.
                            </p>
                        </div>
                    </div>
                    <div class="card shadow-sm border-left-success">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-file-download text-success mr-2"></i>Downloading Backups
                            </h5>
                            <p class="card-text">
                                Download a backup by clicking the download button next to any backup in the list. The SQL file can be used to restore your database.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="card-deck">
                    <div class="card shadow-sm border-left-danger">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-trash text-danger mr-2"></i>Deleting Backups
                            </h5>
                            <p class="card-text">
                                Deleting a backup now moves it to the Trash. You can restore it later or permanently delete it from the Trash.
                            </p>
                        </div>
                    </div>
                    <div class="card shadow-sm border-left-info">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-calendar text-info mr-2"></i>Backup Schedule
                            </h5>
                            <p class="card-text">
                                For best practice, create backups regularly (daily or weekly) and keep multiple versions to ensure data recovery options.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-primary shadow-sm" data-dismiss="modal">
                    <i class="fas fa-check mr-1"></i> Got it
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .badge-pill {
        padding-right: 1em;
        padding-left: 1em;
    }
    
    .font-weight-medium {
        font-weight: 500;
    }
    
    #backupsTable tr:hover {
        background-color: rgba(0,0,0,0.02);
        transform: translateY(-1px);
        transition: all 0.2s ease;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .btn-group .btn {
        box-shadow: none;
    }
    
    .card-header {
        background-color: #f8f9fc;
    }
    
    .border-left-primary {
        border-left: 4px solid #4e73df;
    }
    
    .border-left-success {
        border-left: 4px solid #1cc88a;
    }
    
    .border-left-info {
        border-left: 4px solid #36b9cc;
    }
    
    .border-left-warning {
        border-left: 4px solid #f6c23e;
    }
    
    .border-left-danger {
        border-left: 4px solid #e74a3b;
    }
    
    .opacity-50 {
        opacity: 0.5;
    }
    
    .bg-gradient-light {
        background: linear-gradient(180deg, #f8f9fc 0%, #f1f3f9 100%);
    }
    
    .bg-gradient-primary {
        background: linear-gradient(180deg, #4e73df 0%, #3a56c5 100%);
    }
    
    .bg-gradient-danger {
        background: linear-gradient(180deg, #e74a3b 0%, #c9392b 100%);
    }
    
    .icon-circle {
        height: 2.5rem;
        width: 2.5rem;
        border-radius: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    
    .table td {
        vertical-align: middle;
    }

    #backupsTable thead th {
        border-top: none;
        border-bottom-width: 1px;
    }
    
    #backupsTable {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 0.35rem;
        overflow: hidden;
    }
    
    .stat-card {
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    
    .stat-card:hover i {
        opacity: 0.8 !important;
    }
    
    .empty-state-container {
        padding: 2rem;
        max-width: 500px;
        margin: 0 auto;
    }
    
    .empty-state-icon {
        position: relative;
        display: inline-block;
    }
    
    .empty-state-icon:after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        background: radial-gradient(circle, rgba(78, 115, 223, 0.1) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        z-index: -1;
    }
    
    .btn-icon {
        height: 38px;
        width: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .btn-icon:hover {
        transform: rotate(15deg);
    }
    
    .download-btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .download-btn:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.3);
        transform: translateX(-100%);
        transition: all 0.3s ease;
    }
    
    .download-btn.animating:before {
        animation: slide-shine 1.2s ease-in-out;
    }
    
    .download-btn.animating i {
        animation: bounce-down 0.8s ease;
    }
    
    @keyframes slide-shine {
        0% {
            transform: translateX(-100%);
        }
        40%, 100% {
            transform: translateX(100%);
        }
    }
    
    @keyframes bounce-down {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(5px);
        }
        60% {
            transform: translateY(2px);
        }
    }
    
    /* Ensure Age badge is visible with proper contrast */
    #backupsTable .badge { color: #fff !important; }
    /* Support both badge-* and bg-* variants to cover different Bootstrap/AdminLTE versions */
    #backupsTable .badge-success,
    #backupsTable .badge.bg-success,
    #backupsTable .bg-success.badge { background-color: #1cc88a !important; }
    #backupsTable .badge-info,
    #backupsTable .badge.bg-info,
    #backupsTable .bg-info.badge { background-color: #36b9cc !important; }
    #backupsTable .badge-secondary,
    #backupsTable .badge.bg-secondary,
    #backupsTable .bg-secondary.badge { background-color: #858796 !important; }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize DataTable with more options
        const backupsTable = $('#backupsTable').DataTable({
            "order": [[2, "desc"]], // Sort by date column (index 2) in descending order
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "language": {
                "emptyTable": "No backups available",
                "info": "Showing _START_ to _END_ of _TOTAL_ backups",
                "infoEmpty": "Showing 0 to 0 of 0 backups",
                "search": "<i class='fas fa-search text-muted'></i> _INPUT_",
                "searchPlaceholder": "Search backups..."
            },
            "dom": '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center"f>>t<"d-flex justify-content-between align-items-center mt-3"<"d-flex align-items-center"i><"d-flex align-items-center"p>>',
            "responsive": true,
            "drawCallback": function() {
                // Reinitialize tooltips after table redraw
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
        
        // Delete now handled by direct form POST per row (no JS wiring required)

        // Initialize Trash table if present
        if ($('#trashTable').length) {
            $('#trashTable').DataTable({
                "order": [[2, "desc"]],
                "pageLength": 5,
                "lengthMenu": [[5, 10, 25, -1], [5, 10, 25, "All"]],
                "language": {
                    "emptyTable": "Trash is empty",
                    "info": "Showing _START_ to _END_ of _TOTAL_ trashed backups",
                    "infoEmpty": "Showing 0 to 0 of 0 trashed backups",
                    "search": "<i class='fas fa-search text-muted'></i> _INPUT_",
                    "searchPlaceholder": "Search trash..."
                },
                "dom": '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center"f>>t<"d-flex justify-content-between align-items-center mt-3"<"d-flex align-items-center"i><"d-flex align-items-center"p>>',
                "responsive": true,
                "drawCallback": function() {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });
        }
        
        // Create backup with loading overlay
        $('#createBackupBtn, .dropdown-item:contains("Create New")').click(function(e) {
            $('#loadingOverlay').fadeIn(300);
            
            const $progressBar = $('.progress-bar');
            let progress = 0;
            
            const progressInterval = setInterval(function() {
                progress += Math.random() * 15;
                if (progress > 90) {
                    progress = 90;
                    clearInterval(progressInterval);
                }
                $progressBar.css('width', progress + '%');
            }, 1000);
            
            e.preventDefault();
            
            $.ajax({
                url: "{{ route('database.backup.create') }}",
                type: 'GET',
                success: function(response) {
                    $progressBar.css('width', '100%');
                    clearInterval(progressInterval);
                    
                    setTimeout(function() {
                        window.location.href = "{{ route('database.backups') }}?success=Backup created successfully";
                    }, 500);
                },
                error: function(xhr) {
                    $('#loadingOverlay').fadeOut(300);
                    clearInterval(progressInterval);
                    
                    const errorMessage = xhr.responseJSON && xhr.responseJSON.message 
                        ? xhr.responseJSON.message 
                        : 'An error occurred while creating the backup';
                        
                    const alertHtml = '<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">' +
                        '<div class="d-flex align-items-center">' +
                        '<i class="fas fa-exclamation-circle mr-2 fa-lg"></i>' +
                        '<strong>' + errorMessage + '</strong>' +
                        '</div>' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                        '</div>';
                    
                    $('#alertContainer').html(alertHtml);
                }
            });
        });
        
        // Handle refresh from dropdown only
        $('#refreshBackupsAlt').click(function(e) {
            e.preventDefault();
            const $icon = $(this).find('i');
            $icon.removeClass('fa-sync fa-sync-alt').addClass('fa-spinner fa-spin');
            setTimeout(function() {
                window.location.href = "{{ route('database.backups') }}";
            }, 800);
        });
        
        // Process URL parameters for success messages
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            const successMessage = urlParams.get('success');
            
            const alertHtml = '<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">' +
                '<div class="d-flex align-items-center">' +
                '<i class="fas fa-check-circle mr-2 fa-lg"></i>' +
                '<strong>' + successMessage + '</strong>' +
                '</div>' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '</div>';
            
            $('#alertContainer').html(alertHtml);
            
            // Clean the URL
            history.replaceState({}, document.title, "{{ route('database.backups') }}");
        }

        // Add animation to download buttons
        $('.download-btn').click(function(e) {
            const $btn = $(this);
            $btn.addClass('animating');
            
            setTimeout(function() {
                $btn.removeClass('animating');
            }, 1200);
        });
    });
</script>
@endpush
