<x-app-layout>
    <style>
        .hero-dashboard { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 36px 0; position: relative; overflow: hidden; }
        .hero-dashboard .hero-icon { position: absolute; right: -40px; bottom: -20px; font-size: 10rem; opacity: 0.12; }
        .stat-card { border: 0; box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-radius: 14px; }
    </style>

    <section class="hero-dashboard">
        <div class="container d-flex align-items-center justify-content-between">
            <div>
                <h1 class="display-6 fw-bold mb-1">Analytics</h1>
                <p class="mb-0 opacity-75">Visualize your progress and strengths.</p>
            </div>
            <i class="fa-solid fa-chart-line hero-icon"></i>
        </div>
    </section>

    <div class="container py-4">
        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <div class="card stat-card">
                    <div class="card-header fw-semibold"><i class="fa-solid fa-chart-line me-2"></i>Scores Over Time</div>
                    <div class="card-body">
                        <canvas id="scoreTrendChart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card stat-card">
                    <div class="card-header fw-semibold"><i class="fa-solid fa-chart-pie me-2"></i>Category Performance</div>
                    <div class="card-body">
                        <canvas id="categoryPie" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        // Line chart: scores vs time
        new Chart(document.getElementById('scoreTrendChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($trendLabels) !!},
                datasets: [{
                    label: 'Score %',
                    data: {!! json_encode($scoreTrends) !!},
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.2)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {responsive:true, plugins:{legend:{display:false}}}
        });
        // Pie chart: category-wise
        new Chart(document.getElementById('categoryPie'), {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_keys($categoryStats)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($categoryStats)) !!},
                    backgroundColor: ['#dc3545','#0d6efd']
                }]
            },
            options: {responsive:true, plugins:{legend:{position:'bottom'}}}
        });
    </script>
</x-app-layout>
