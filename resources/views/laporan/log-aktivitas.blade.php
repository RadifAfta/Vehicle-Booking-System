@extends('layouts.app')

@section('content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Log Aktivitas User</h1>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Filter Data</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.log-aktivitas') }}" method="GET">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Pengguna</label>
                        <select name="user_id" class="form-control">
                            <option value="">Semua Pengguna</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (string) $userId === (string) $user->id ? 'selected' : '' }}>{{ $user->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Data Log Aktivitas</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Jenis Aksi</th>
                    <th>Aktivitas</th>
                    <th>Halaman</th>
                </tr>
                </thead>
                <tbody>
                @forelse($logList as $log)
                    @php
                        $aksiLabel = match (strtoupper($log->method)) {
                            'POST' => 'Simpan',
                            'PATCH', 'PUT' => 'Ubah',
                            'DELETE' => 'Hapus',
                            default => 'Lihat',
                        };

                        $aksiClass = match (strtoupper($log->method)) {
                            'POST' => 'badge-success',
                            'PATCH', 'PUT' => 'badge-warning',
                            'DELETE' => 'badge-danger',
                            default => 'badge-info',
                        };

                        $halaman = match ($log->route_name) {
                            'dashboard' => 'Dashboard',
                            'pemesanan.index', 'pemesanan.store', 'pemesanan.riwayat.store' => 'Pemesanan Kendaraan',
                            'persetujuan.index', 'persetujuan.update' => 'Persetujuan',
                            'laporan.index', 'laporan.export' => 'Periodik Pemesanan',
                            'laporan.log-persetujuan' => 'Log Persetujuan',
                            'laporan.log-aktivitas' => 'Log Aktivitas',
                            default => 'Halaman Lainnya',
                        };
                    @endphp
                    <tr>
                        <td>{{ $log->created_at?->format('d-m-Y H:i') }}</td>
                        <td>{{ $log->user?->nama ?? '-' }}</td>
                        <td><span class="badge {{ $aksiClass }}">{{ $aksiLabel }}</span></td>
                        <td>{{ $log->aktivitas }}</td>
                        <td>{{ $halaman }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada aktivitas tercatat.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
