@extends('layouts.app')

@section('title', 'Dashboard')
@section('content')
<div class="container-fluid">
    <!-- Dashboard Analytics Cards -->
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
                    <h2 class="mb-2">245</h2>
                    <p class="small text-muted mb-0"><i class="fas fa-arrow-up text-success me-1"></i> 12% from last month</p>
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
                    <h2 class="mb-2">42</h2>
                    <p class="small text-muted mb-0"><i class="fas fa-arrow-down text-danger me-1"></i> 3% from last week</p>
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
                    <h2 class="mb-2">189</h2>
                    <p class="small text-muted mb-0"><i class="fas fa-arrow-up text-success me-1"></i> 8% from last month</p>
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
                    <h2 class="mb-2">37</h2>
                    <p class="small text-muted mb-0"><i class="fas fa-minus text-muted me-1"></i> Same as last week</p>
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
                    <h2 class="mb-2">156</h2>
                    <p class="small text-muted mb-0"><i class="fas fa-arrow-up text-success me-1"></i> 15% increase</p>
                </div>
            </div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-danger bg-opacity-10 me-3">
                            <i class="fas fa-calendar-alt text-danger"></i>
                        </div>
                        <h6 class="card-title mb-0">Deadlines</h6>
                    </div>
                    <h2 class="mb-2">12</h2>
                    <p class="small text-muted mb-0">Due in next 3 days</p>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Filter Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0">
                <div class="card-body">
                    <form class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <label class="visually-hidden" for="searchQuery">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchQuery" placeholder="Search instructions...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="visually-hidden" for="statusFilter">Status</label>
                            <select class="form-select" id="statusFilter">
                                <option selected value="">Status: All</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="forwarded">Forwarded</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="visually-hidden" for="dateRange">Date Range</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
                                <input type="text" class="form-control" id="dateRange" placeholder="Date range">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        </div>
                        <div class="col-md-2">
                            <button type="reset" class="btn btn-outline-secondary w-100">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Sections -->
    <div class="row">
        <!-- My Assigned Instructions -->
        <div class="col-lg-8">
            <div class="card border-0 mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks me-2 text-primary"></i>
                        My Assigned Instructions
                    </h5>
                    <span class="badge bg-primary rounded-pill">42 Active</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">From</th>
                                    <th scope="col">Given Date</th>
                                    <th scope="col">Deadline</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Feedback</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="#" class="fw-bold text-primary">#INS-1094</a></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=John+Doe&background=4070f4&color=fff" class="rounded-circle me-2" width="32" height="32">
                                            <span>John Doe</span>
                                        </div>
                                    </td>
                                    <td>May 15, 2023</td>
                                    <td><span class="text-danger fw-bold">May 29, 2023</span></td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td><span class="badge bg-light text-dark">2 Comments</span></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-comment"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-share"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="fw-bold text-primary">#INS-1093</a></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=Jane+Smith&background=4070f4&color=fff" class="rounded-circle me-2" width="32" height="32">
                                            <span>Jane Smith</span>
                                        </div>
                                    </td>
                                    <td>May 12, 2023</td>
                                    <td>June 12, 2023</td>
                                    <td><span class="badge bg-info">In Progress</span></td>
                                    <td><span class="badge bg-light text-dark">1 Comment</span></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-comment"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-share"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="fw-bold text-primary">#INS-1092</a></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=Robert+Johnson&background=4070f4&color=fff" class="rounded-circle me-2" width="32" height="32">
                                            <span>Robert Johnson</span>
                                        </div>
                                    </td>
                                    <td>May 10, 2023</td>
                                    <td><span class="text-danger fw-bold">May 30, 2023</span></td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td><span class="badge bg-light text-dark">0 Comments</span></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-comment"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-share"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="fw-bold text-primary">#INS-1091</a></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=Sarah+Williams&background=4070f4&color=fff" class="rounded-circle me-2" width="32" height="32">
                                            <span>Sarah Williams</span>
                                        </div>
                                    </td>
                                    <td>May 8, 2023</td>
                                    <td>May 22, 2023</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td><span class="badge bg-light text-dark">5 Comments</span></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-comment"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-share"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="fw-bold text-primary">#INS-1090</a></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=Michael+Brown&background=4070f4&color=fff" class="rounded-circle me-2" width="32" height="32">
                                            <span>Michael Brown</span>
                                        </div>
                                    </td>
                                    <td>May 5, 2023</td>
                                    <td>May 19, 2023</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td><span class="badge bg-light text-dark">3 Comments</span></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-comment"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-share"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent text-center">
                    <a href="#" class="btn btn-sm btn-link text-primary text-decoration-none">View All Instructions <i class="fas fa-arrow-right ms-1"></i></a>
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
                    <span class="badge bg-info rounded-pill">12 New</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item border-0 px-3 py-3">
                            <div class="d-flex">
                                <img src="https://ui-avatars.com/api/?name=John+Doe&background=4070f4&color=fff" class="rounded-circle me-3" width="40" height="40">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <h6 class="mb-0 me-2">John Doe</h6>
                                        <span class="badge bg-light text-dark">#INS-1094</span>
                                        <small class="text-muted ms-auto">2 hours ago</small>
                                    </div>
                                    <p class="mb-0 text-muted">Please review the quarterly compliance report and provide feedback by end of day.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0 px-3 py-3 bg-light bg-opacity-50">
                            <div class="d-flex">
                                <img src="https://ui-avatars.com/api/?name=Sarah+Williams&background=4070f4&color=fff" class="rounded-circle me-3" width="40" height="40">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <h6 class="mb-0 me-2">Sarah Williams</h6>
                                        <span class="badge bg-light text-dark">#INS-1091</span>
                                        <small class="text-muted ms-auto">5 hours ago</small>
                                    </div>
                                    <p class="mb-0 text-muted">All issues from the previous audit have been addressed. Documentation attached for your review.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0 px-3 py-3">
                            <div class="d-flex">
                                <img src="https://ui-avatars.com/api/?name=Robert+Johnson&background=4070f4&color=fff" class="rounded-circle me-3" width="40" height="40">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <h6 class="mb-0 me-2">Robert Johnson</h6>
                                        <span class="badge bg-light text-dark">#INS-1092</span>
                                        <small class="text-muted ms-auto">Yesterday</small>
                                    </div>
                                    <p class="mb-0 text-muted">We need to schedule a meeting to discuss the new regulatory changes that will affect our reporting process.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0 px-3 py-3 bg-light bg-opacity-50">
                            <div class="d-flex">
                                <img src="https://ui-avatars.com/api/?name=Michael+Brown&background=4070f4&color=fff" class="rounded-circle me-3" width="40" height="40">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <h6 class="mb-0 me-2">Michael Brown</h6>
                                        <span class="badge bg-light text-dark">#INS-1090</span>
                                        <small class="text-muted ms-auto">2 days ago</small>
                                    </div>
                                    <p class="mb-0 text-muted">The compliance report has been approved. Thank you for addressing all the concerns promptly.</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent text-center">
                    <a href="#" class="btn btn-sm btn-link text-info text-decoration-none">View All Feedbacks <i class="fas fa-arrow-right ms-1"></i></a>
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
                        <li class="list-group-item border-0 px-3 py-3 d-flex align-items-center">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="transfer-icon me-3">
                                    <i class="fas fa-arrow-right text-info"></i>
                                </div>
                                <div>
                                    <p class="mb-1 fw-medium">#INS-1087 → Jane Smith</p>
                                    <small class="text-muted">Forwarded on May 18, 2023</small>
                                </div>
                            </div>
                            <span class="badge bg-info">In Review</span>
                        </li>
                        <li class="list-group-item border-0 px-3 py-3 d-flex align-items-center bg-light bg-opacity-50">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="transfer-icon me-3">
                                    <i class="fas fa-arrow-right text-info"></i>
                                </div>
                                <div>
                                    <p class="mb-1 fw-medium">#INS-1084 → Robert Johnson</p>
                                    <small class="text-muted">Forwarded on May 15, 2023</small>
                                </div>
                            </div>
                            <span class="badge bg-success">Completed</span>
                        </li>
                        <li class="list-group-item border-0 px-3 py-3 d-flex align-items-center">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="transfer-icon me-3">
                                    <i class="fas fa-arrow-right text-info"></i>
                                </div>
                                <div>
                                    <p class="mb-1 fw-medium">#INS-1079 → Sarah Williams</p>
                                    <small class="text-muted">Forwarded on May 10, 2023</small>
                                </div>
                            </div>
                            <span class="badge bg-warning text-dark">Pending</span>
                        </li>
                        <li class="list-group-item border-0 px-3 py-3 d-flex align-items-center bg-light bg-opacity-50">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="transfer-icon me-3">
                                    <i class="fas fa-arrow-right text-info"></i>
                                </div>
                                <div>
                                    <p class="mb-1 fw-medium">#INS-1075 → Michael Brown</p>
                                    <small class="text-muted">Forwarded on May 5, 2023</small>
                                </div>
                            </div>
                            <span class="badge bg-success">Completed</span>
                        </li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent text-center">
                    <a href="#" class="btn btn-sm btn-link text-success text-decoration-none">View All Forwarded <i class="fas fa-arrow-right ms-1"></i></a>
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
            labels: ['Apr 25', 'Apr 30', 'May 5', 'May 10', 'May 15', 'May 20', 'May 25'],
            datasets: [
                {
                    label: 'Total Instructions',
                    data: [180, 190, 205, 215, 225, 235, 245],
                    borderColor: '#4070f4',
                    backgroundColor: 'rgba(64, 112, 244, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Completed',
                    data: [120, 135, 145, 155, 165, 175, 189],
                    borderColor: '#2dce89',
                    backgroundColor: 'transparent',
                    tension: 0.4,
                    borderDash: [5, 5]
                },
                {
                    label: 'Pending',
                    data: [60, 55, 60, 60, 60, 60, 42],
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
            labels: ['Completed', 'Pending', 'In Progress', 'Forwarded', 'Delayed'],
            datasets: [{
                data: [189, 42, 15, 37, 5],
                backgroundColor: [
                    '#2dce89',
                    '#ffc107',
                    '#11cdef',
                    '#5e72e4',
                    '#fb6340'
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
            background: var(--bg-hover);
            display: flex;
            align-items: center;
            justify-content: center;
        }
    `;
    document.head.appendChild(style);
});
</script>
@endsection
