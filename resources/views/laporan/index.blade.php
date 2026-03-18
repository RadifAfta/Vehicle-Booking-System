@extends('layouts.app')

@section('content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Laporan Periodik Pemesanan</h1>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Filter Laporan</h3>
        </div>
        <div class="card-body">
        <form action="{{ route('laporan.index') }}" method="GET">
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                </div>
                <div class="col-md-6 form-group d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">Filter</button>
                    <a href="{{ route('laporan.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success">Export Excel</a>
                </div>
            </div>
        </form>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Data Pemesanan</h3>
        </div>
        <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap mb-0">
            <thead>
            <tr>
                <th>ID</th>
                <th>Admin</th>
                <th>Kendaraan</th>
                <th>Driver</th>
                <th>Mulai</th>
                <th>Selesai</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse($pemesananList as $pemesanan)
                @php
                    $statusClass = match ($pemesanan->status_pemesanan) {
                        'menunggu_persetujuan' => 'status-menunggu',
                        'disetujui_level_1' => 'status-level1',
                        'disetujui_final' => 'status-final',
                        'ditolak' => 'status-ditolak',
                        default => 'status-menunggu',
                    };
                @endphp
                <tr>
                    <td>{{ $pemesanan->id }}</td>
                    <td>{{ $pemesanan->admin->nama }}</td>
                    <td>{{ $pemesanan->kendaraan->nama }}</td>
                    <td>{{ $pemesanan->driver->nama }}</td>
                    <td>{{ $pemesanan->tanggal_mulai }}</td>
                    <td>{{ $pemesanan->tanggal_selesai }}</td>
                    <td><span class="status-pill {{ $statusClass }}">{{ $pemesanan->status_pemesanan }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
