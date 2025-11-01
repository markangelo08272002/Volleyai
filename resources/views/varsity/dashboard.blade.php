@extends('layouts.app')

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <span class="badge bg-primary-subtle text-primary-emphasis mb-2"><i class="bi bi-activity"></i> Activity Score</span>
                <h3 class="fw-bold mb-1">{{ !empty($performanceData) ? ($performanceData[0]['score'] ?? 'N/A') : 'N/A' }}</h3>
                <p class="text-secondary mb-0 small">Your latest performance rating</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <span class="badge bg-success-subtle text-success-emphasis mb-2"><i class="bi bi-graph-up-arrow"></i> Improvement</span>
                <h3 class="fw-bold mb-1">
                    @php
                        $improvement = 'N/A';
                        $count = !empty($performanceData) ? count($performanceData) : 0;
                        if ($count >= 2) {
                            $latestScore = $performanceData[$count - 1]['score'];
                            $previousScore = $performanceData[$count - 2]['score'];
                            if ($previousScore > 0) {
                                $percentageChange = round((($latestScore - $previousScore) / $previousScore) * 100);
                                $improvement = ($percentageChange > 0 ? '+' : '') . $percentageChange . '%';
                            }
                        }
                    @endphp
                    {{ $improvement }}
                </h3>
                <p class="text-secondary mb-0 small">Vs. previous session</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <span class="badge bg-info-subtle text-info-emphasis mb-2"><i class="bi bi-clock-history"></i> Total Sessions</span>
                <h3 class="fw-bold mb-1">{{ !empty($performanceData) ? count($performanceData) : 0 }}</h3>
                <p class="text-secondary mb-0 small">Completed drills</p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-line-fill me-2"></i>Performance Trends</h5>
    </div>
    <div class="card-body">
        <canvas id="performanceChart" style="height:150px;"></canvas>
    </div>
</div>


@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Performance Chart Logic
    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        // Add a check for performanceData existence
        const performanceData = @json($performanceData ?? []); 
        
        if (performanceData.length > 0) {
            const labels = performanceData.map(data => data.date);
            const scores = performanceData.map(data => data.score);

            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Performance Score',
                        data: scores,
                        fill: true,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: '#3b82f6',
                        tension: 0.4,
                        pointBackgroundColor: '#3b82f6',
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: '#9ca3af' }, grid: { display: false } },
                        y: { ticks: { color: '#9ca3af' }, grid: { color: '#4b5563' }, min: 0, max: 100 }
                    }
                }
            });
        } else {
            // Optional: Show a message if no data
            ctx.parentElement.innerHTML = '<p class="text-secondary text-center">No performance data yet to display a chart.</p>';
        }
    }
});
</script>
@endpush