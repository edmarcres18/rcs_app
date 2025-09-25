@extends('layouts.app')

@section('title', 'Dashboard')
@section('content')
<div class="container-fluid">
    <!-- Role-specific Dashboard Analytics Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Instructions -->
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-primary bg-opacity-10 me-3">
                            <i class="fas fa-clipboard text-primary"></i>
                        </div>
                        <h6 class="card-title mb-0">Total Instructions</h6>
                    </div>
                    <h2 class="mb-2">{{ $totalInstructions }}</h2>
                    <p class="small text-muted mb-0">
                        @if(isset($isAdmin) || isset($isSystemAdmin))
                        <i class="fas fa-chart-line text-primary me-1"></i> System Total
                        @else
                        <i class="fas fa-user text-primary me-1"></i> Assigned to You
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Pending Instructions -->
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-warning bg-opacity-10 me-3">
                            <i class="fas fa-hourglass-half text-warning"></i>
                        </div>
                        <h6 class="card-title mb-0">Pending</h6>
                    </div>
                    <h2 class="mb-2">{{ $pendingInstructions }}</h2>
                    <p class="small text-muted mb-0">
                        @if($pendingInstructions > 0)
                        <i class="fas fa-exclamation-circle text-warning me-1"></i> Awaiting action
                        @else
                        <i class="fas fa-check text-success me-1"></i> All clear
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Completed Instructions -->
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-success bg-opacity-10 me-3">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <h6 class="card-title mb-0">Completed</h6>
                    </div>
                    <h2 class="mb-2">{{ $completedInstructions }}</h2>
                    <p class="small text-muted mb-0">
                        @if($totalInstructions > 0)
                        <i class="fas fa-chart-pie text-success me-1"></i>
                        {{ round(($completedInstructions / $totalInstructions) * 100) }}% completion
                        @else
                        <i class="fas fa-chart-pie text-success me-1"></i> 0% completion
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Forwarded Instructions -->
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-info bg-opacity-10 me-3">
                            <i class="fas fa-share text-info"></i>
                        </div>
                        <h6 class="card-title mb-0">Forwarded</h6>
                    </div>
                    <h2 class="mb-2">{{ $forwardedInstructions }}</h2>
                    <p class="small text-muted mb-0">
                        @if(isset($isSupervisor))
                        <i class="fas fa-random text-info me-1"></i> Delegated tasks
                        @elseif(isset($isAdmin) || isset($isSystemAdmin))
                        <i class="fas fa-random text-info me-1"></i> System total
                        @else
                        <i class="fas fa-arrow-right text-info me-1"></i> Received
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Feedback Submitted -->
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-secondary bg-opacity-10 me-3">
                            <i class="fas fa-comment-alt text-secondary"></i>
                        </div>
                        <h6 class="card-title mb-0">Feedback</h6>
                    </div>
                    <h2 class="mb-2">{{ $feedbackCount }}</h2>
                    <p class="small text-muted mb-0">
                        <i class="fas fa-comments text-secondary me-1"></i> Total replies
                    </p>
                </div>
            </div>
        </div>

        <!-- Upcoming Deadlines or System-specific metric -->
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0">
                <div class="card-body">
                    @if(isset($isSystemAdmin))
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-primary bg-opacity-10 me-3">
                            <i class="fas fa-users text-primary"></i>
                        </div>
                        <h6 class="card-title mb-0">Active Users</h6>
                    </div>
                    <h2 class="mb-2">{{ $activeUsers }}</h2>
                    <p class="small text-muted mb-0">
                        <i class="fas fa-calendar text-primary me-1"></i> Last 7 days
                    </p>
                    @else
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-danger bg-opacity-10 me-3">
                            <i class="fas fa-calendar-alt text-danger"></i>
                        </div>
                        <h6 class="card-title mb-0">Deadlines</h6>
                    </div>
                    <h2 class="mb-2">{{ $upcomingDeadlines }}</h2>
                    <p class="small text-muted mb-0">
                        <i class="fas fa-clock text-danger me-1"></i> Due soon
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(isset($isSystemAdmin))
    <!-- System Admin specific cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0">
                <div class="card-body">
                    <h6 class="card-title mb-3">User Distribution</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Employees</span>
                        <span class="fw-bold">{{ $usersByRole['employees'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Supervisors</span>
                        <span class="fw-bold">{{ $usersByRole['supervisors'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Admins</span>
                        <span class="fw-bold">{{ $usersByRole['admins'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">System Admins</span>
                        <span class="fw-bold">{{ $usersByRole['systemAdmins'] }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0">
                <div class="card-body">
                    <h6 class="card-title mb-3">Instruction Activity</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Today</span>
                        <span class="fw-bold">{{ $systemStats['dailyInstructions'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">This Week</span>
                        <span class="fw-bold">{{ $systemStats['weeklyInstructions'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">This Month</span>
                        <span class="fw-bold">{{ $systemStats['monthlyInstructions'] }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0">
                <div class="card-body">
                    <h6 class="card-title mb-3">System Performance</h6>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div class="text-center">
                            <h5 class="mb-0">75%</h5>
                            <small class="text-muted">System Load</small>
                        </div>
                        <div class="text-center">
                            <h5 class="mb-0">3.2s</h5>
                            <small class="text-muted">Avg Response</small>
                        </div>
                        <div class="text-center">
                            <h5 class="mb-0">99.9%</h5>
                            <small class="text-muted">Uptime</small>
                        </div>
                        <div class="text-center">
                            <h5 class="mb-0">12</h5>
                            <small class="text-muted">Active Sessions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Analytics Chart -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title">Instruction Trends</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartRange" data-bs-toggle="dropdown" aria-expanded="false">
                                Last 30 Days
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="chartRange">
                                <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                                <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                                <li><a class="dropdown-item" href="#">Last Quarter</a></li>
                                <li><a class="dropdown-item" href="#">This Year</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="instructionTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title">Status Distribution</h5>
                        <div>
                            <button class="btn btn-sm btn-link text-decoration-none p-0" data-bs-toggle="tooltip" title="Refresh Data">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="statusDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Main Content Sections -->
    <div class="row">
        <!-- My Instructions Section -->
        <div class="col-lg-8">
            <div class="card border-0 mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks me-2 text-primary"></i>
                        @if(isset($isAdmin) || isset($isSystemAdmin))
                        Recent Instructions
                        @else
                        My Assigned Instructions
                        @endif
                    </h5>
                    <span class="badge bg-primary rounded-pill">
                        @if(isset($isAdmin) || isset($isSystemAdmin))
                        {{ $recentInstructions->count() }} Recent
                        @else
                        {{ $pendingInstructions }} Active
                        @endif
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">From</th>
                                    <th scope="col">Given Date</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Feedback</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentInstructions as $instruction)
                                <tr>
                                    <td><a href="{{ route('instructions.show', $instruction) }}" class="fw-bold text-primary">#INS-{{ $instruction->id }}</a></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($instruction->sender->full_name) }}&background=4070f4&color=fff" class="rounded-circle me-2" width="32" height="32">
                                            <span>{{ $instruction->sender->full_name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $instruction->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @php
                                        $status = 'Pending';
                                        $badgeClass = 'bg-warning text-dark';

                                        // Check if instruction has been replied or forwarded by the current user
                                        $hasUserActivity = $instruction->activities()
                                            ->where('user_id', auth()->id())
                                            ->whereIn('action', ['replied', 'forwarded'])
                                            ->exists();

                                        if ($hasUserActivity) {
                                            $status = 'Completed';
                                            $badgeClass = 'bg-success';
                                        } elseif ($instruction->replies()->exists()) {
                                            $status = 'In Progress';
                                            $badgeClass = 'bg-info';
                                        } elseif ($instruction->target_deadline && $instruction->target_deadline < now()) {
                                            $status = 'Delayed';
                                            $badgeClass = 'bg-danger';
                                        }
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $instruction->replies()->count() }} {{ Str::plural('Comment', $instruction->replies()->count()) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('instructions.show', $instruction) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('instructions.show', $instruction) }}#reply" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-comment"></i>
                                            </a>
                                            <a href="{{ route('instructions.show-forward', $instruction) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-share"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h6 class="fw-light text-muted">No instructions found</h6>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent text-center">
                    <a href="{{ route('instructions.index') }}" class="btn btn-sm btn-link text-primary text-decoration-none">View All Instructions <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Recent Feedbacks -->
            <div class="card border-0 mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-comments me-2 text-info"></i>
                        Recent Feedbacks
                    </h5>
                    <span class="badge bg-info rounded-pill">{{ $recentFeedbacks->count() }} New</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($recentFeedbacks as $feedback)
                        <li class="list-group-item border-0 px-3 py-3 {{ $loop->even ? 'bg-light bg-opacity-50' : '' }}">
                            <div class="d-flex">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($feedback->user->full_name) }}&background=4070f4&color=fff" class="rounded-circle me-3" width="40" height="40">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <h6 class="mb-0 me-2">{{ $feedback->user->full_name }}</h6>
                                        <span class="badge bg-light text-dark">#INS-{{ $feedback->instruction_id }}</span>
                                        <small class="text-muted ms-auto">{{ $feedback->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0 text-muted">{{ Str::limit($feedback->content, 100) }}</p>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="list-group-item border-0 px-3 py-4">
                            <div class="text-center">
                                <i class="fas fa-comment-slash fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No feedback messages yet</p>
                            </div>
                        </li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer bg-transparent text-center">
                    <a href="{{ route('instructions.index') }}" class="btn btn-sm btn-link text-info text-decoration-none">View All Feedbacks <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>

            <!-- Forwarded Instructions -->
            <div class="card border-0">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exchange-alt me-2 text-success"></i>
                        Forwarded Instructions
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($forwardedInstructionList as $forwarded)
                        <li class="list-group-item border-0 px-3 py-3 d-flex align-items-center {{ $loop->even ? 'bg-light bg-opacity-50' : '' }}">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="transfer-icon me-3">
                                    <i class="fas fa-arrow-right text-info"></i>
                                </div>
                                <div>
                                    <p class="mb-1 fw-medium">#INS-{{ $forwarded->id }}
                                    @if(isset($isAdmin) || isset($isSystemAdmin) || isset($isSupervisor))
                                        @php
                                            $recipient = $forwarded->recipients->first();
                                        @endphp
                                        → {{ $recipient ? $recipient->full_name : 'Unknown' }}
                                    @endif
                                    </p>
                                    <small class="text-muted">Forwarded on {{ $forwarded->updated_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                            @php
                                $status = 'Pending';
                                $badgeClass = 'bg-warning text-dark';

                                // Check if instruction has been replied or forwarded by the current user
                                $hasUserActivity = $forwarded->activities()
                                    ->where('user_id', auth()->id())
                                    ->whereIn('action', ['replied', 'forwarded'])
                                    ->exists();

                                if ($hasUserActivity) {
                                    $status = 'Completed';
                                    $badgeClass = 'bg-success';
                                } elseif ($forwarded->replies()->exists()) {
                                    $status = 'In Review';
                                    $badgeClass = 'bg-info';
                                } elseif ($forwarded->target_deadline && $forwarded->target_deadline < now()) {
                                    $status = 'Delayed';
                                    $badgeClass = 'bg-danger';
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                        </li>
                        @empty
                        <li class="list-group-item border-0 px-3 py-4">
                            <div class="text-center">
                                <i class="fas fa-random fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No forwarded instructions</p>
                            </div>
                        </li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer bg-transparent text-center">
                    <a href="{{ route('instructions.index') }}" class="btn btn-sm btn-link text-success text-decoration-none">View All Forwarded <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Bootstrap-compatible JS for Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Instructions Trend Chart
    const trendsCtx = document.getElementById('instructionTrendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: @json($trendData['labels']),
            datasets: [
                {
                    label: 'Total Instructions',
                    data: @json($trendData['totalData']),
                    borderColor: '#4070f4',
                    backgroundColor: 'rgba(64, 112, 244, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Completed',
                    data: @json($trendData['completedData']),
                    borderColor: '#2dce89',
                    backgroundColor: 'transparent',
                    tension: 0.4,
                    borderDash: [5, 5]
                },
                {
                    label: 'Pending',
                    data: @json($trendData['pendingData']),
                    borderColor: '#ffc107',
                    backgroundColor: 'transparent',
                    tension: 0.4,
                    borderDash: [5, 5]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [3, 3],
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    }
                }
            }
        }
    });

    // Status Distribution Chart
    const distributionCtx = document.getElementById('statusDistributionChart').getContext('2d');
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: @json($statusDistribution['labels']),
            datasets: [{
                data: @json($statusDistribution['data']),
                backgroundColor: [
                    '#2dce89', // Completed
                    '#ffc107', // Pending
                    '#11cdef', // In Progress
                    '#5e72e4', // Forwarded
                    '#fb6340'  // Delayed
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
                }
            }
        }
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Initialize date range picker if available
    if(typeof daterangepicker !== 'undefined' && $('#dateRange').length) {
        $('#dateRange').daterangepicker({
            opens: 'left',
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    }

    // Style modifications for light/dark mode compatibility
    const updateChartsTheme = () => {
        const theme = document.documentElement.getAttribute('data-theme');
        const textColor = theme === 'dark' ? '#e4e6eb' : '#333';
        const gridColor = theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

        // Get all chart instances using Object.values from the registry
        Object.values(Chart.instances).forEach(chart => {
            if (chart.config.type === 'line' || chart.config.type === 'bar') {
                chart.options.scales.x.ticks.color = textColor;
                chart.options.scales.y.ticks.color = textColor;
                chart.options.scales.x.grid.color = gridColor;
                chart.options.scales.y.grid.color = gridColor;
            }

            chart.options.plugins.legend.labels.color = textColor;
            chart.update();
        });
    };

    // Initial call to set chart theme
    updateChartsTheme();

    // Listen for theme changes
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            // Allow time for the theme to update
            setTimeout(updateChartsTheme, 100);
        });
    }

    // Define class for icon-circle
    const style = document.createElement('style');
    style.textContent = `
        .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-circle i {
            font-size: 18px;
        }
        .transfer-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--bg-hover, #f8f9fa);
            display: flex;
            align-items: center;
            justify-content: center;
        }
    `;
    document.head.appendChild(style);
});
</script>
@endsection
