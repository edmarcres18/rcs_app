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
    #backupsTable .badge {
        color: #fff !important;
    }
    #backupsTable .badge-success {
        background-color: #1cc88a !important; /* match theme success */
    }
    #backupsTable .badge-info {
        background-color: #36b9cc !important; /* match theme info */
    }
    #backupsTable .badge-secondary {
        background-color: #858796 !important; /* match theme secondary */
    }
</style>
@endsection
