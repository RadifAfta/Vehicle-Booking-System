@extends('layouts.app')

@section('content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Laporan Log Persetujuan</h1>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Filter Tanggal</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.log-persetujuan') }}" method="GET">
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
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Data Log Persetujuan</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover text-nowrap mb-0">
                <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pemesanan ID</th>
                    <th>Admin</th>
                    <th>Kendaraan</th>
                    <th>Penyetuju</th>
                    <th>Level</th>
                    <th>Aksi</th>
                    <th>Catatan</th>
                </tr>
                </thead>
                <tbody>
                @forelse($logList as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->pemesanan_id }}</td>
                        <td>{{ $log->pemesanan?->admin?->nama ?? '-' }}</td>
                        <td>{{ $log->pemesanan?->kendaraan?->nama ?? '-' }}</td>
                        <td>{{ $log->penyetujui?->nama ?? '-' }}</td>
                        <td>{{ $log->level }}</td>
                        <td>{{ strtoupper($log->aksi) }}</td>
                        <td>{{ $log->catatan_tambahan ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
