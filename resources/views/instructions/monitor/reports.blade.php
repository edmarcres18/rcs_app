@extends('layouts.app')

@section('title', 'Instruction Reports')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">Instruction Reports</h1>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('instructions.monitor') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <a href="{{ route('instructions.monitor.all-activities') }}" class="btn btn-info ms-2">
                <i class="fas fa-history me-1"></i> Activity Logs
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Monthly Activity Chart -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Instruction Activity</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- User Compliance Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Compliance Report</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Total Assigned</th>
                                    <th>Read</th>
                                    <th>Unread</th>
                                    <th>Compliance Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userStats as $stat)
                                    <tr>
                                        <td>{{ $stat->first_name }} {{ $stat->last_name }}</td>
                                        <td>{{ $stat->total_assigned }}</td>
                                        <td>{{ $stat->read_count }}</td>
                                        <td>{{ $stat->unread_count }}</td>
                                        <td>
                                            @php
                                                $rate = $stat->total_assigned > 0 ? ($stat->read_count / $stat->total_assigned) * 100 : 0;
                                            @endphp
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                                    <div class="progress-bar
                                                        @if($rate >= 75) bg-success
                                                        @elseif($rate >= 50) bg-warning
                                                        @else bg-danger
                                                        @endif"
                                                        role="progressbar"
                                                        style="width: {{ $rate }}%;"
                                                        aria-valuenow="{{ $rate }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span>{{ number_format($rate, 1) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Compliance Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Compliance Summary</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container mb-4" style="height: 200px;">
                        <canvas id="complianceChart"></canvas>
                    </div>

                    @php
                        $totalAssigned = $userStats->sum('total_assigned');
                        $totalRead = $userStats->sum('read_count');
                        $overallRate = $totalAssigned > 0 ? ($totalRead / $totalAssigned) * 100 : 0;
                    @endphp

                    <div class="mt-4">
                        <h5 class="text-center mb-3">Overall Compliance Rate</h5>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar
                                @if($overallRate >= 75) bg-success
                                @elseif($overallRate >= 50) bg-warning
                                @else bg-danger
                                @endif"
                                role="progressbar"
                                style="width: {{ $overallRate }}%;"
                                aria-valuenow="{{ $overallRate }}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                                {{ number_format($overallRate, 1) }}%
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalRead }}</div>
                                <div class="small text-muted">Instructions Read</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalAssigned - $totalRead }}</div>
                                <div class="small text-muted">Instructions Unread</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Export Reports</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-file-excel me-2 text-success"></i>
                                Export User Compliance Report
                            </span>
                            <i class="fas fa-download"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-file-excel me-2 text-success"></i>
                                Export Monthly Activity Data
                            </span>
                            <i class="fas fa-download"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-file-pdf me-2 text-danger"></i>
                                Generate PDF Report
                            </span>
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Chart
        const monthlyData = @json($monthlyStats);
        const labels = monthlyData.map(item => {
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return monthNames[item.month - 1] + ' ' + item.year;
        }).reverse();

        const counts = monthlyData.map(item => item.count).reverse();

        const monthlyChart = new Chart(
            document.getElementById('monthlyChart'),
            {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Instructions Created',
                        data: counts,
                        backgroundColor: 'rgba(64, 112, 244, 0.7)',
                        borderColor: 'rgba(64, 112, 244, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            }
        );

        // Compliance Chart
        const complianceChart = new Chart(
            document.getElementById('complianceChart'),
            {
                type: 'doughnut',
                data: {
                    labels: ['Read', 'Unread'],
                    datasets: [{
                        label: 'Instruction Status',
                        data: [{{ $userStats->sum('read_count') }}, {{ $userStats->sum('unread_count') }}],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.7)',
                            'rgba(220, 53, 69, 0.7)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            }
        );
    });
</script>
@endsection
