@extends('layouts.app')

@section('content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalKendaraan }}</h3>
                    <p>Total Kendaraan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-car"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalPemesanan }}</h3>
                    <p>Total Pemesanan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title font-weight-semibold">Grafik Pemakaian Kendaraan (Total KM)</h3>
        </div>
        <div class="card-body">
            <canvas id="usageChart" height="90"></canvas>
        </div>
    </div>

    <script>
        const labels = @json($chartLabels);
        const values = @json($chartValues);
        const ctx = document.getElementById('usageChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Total KM',
                    data: values,
                    borderWidth: 1,
                    borderRadius: 8,
                    backgroundColor: '#4f46e5'
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endsection
