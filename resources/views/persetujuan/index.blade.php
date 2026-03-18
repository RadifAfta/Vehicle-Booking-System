@extends('layouts.app')

@section('content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Persetujuan Pemesanan</h1>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Daftar Persetujuan</h3>
        </div>
        <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap mb-0">
            <thead>
            <tr>
                <th>ID</th>
                <th>Admin</th>
                <th>Kendaraan</th>
                <th>Driver</th>
                <th>Status</th>
                <th>Aksi</th>
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
                    <td><span class="status-pill {{ $statusClass }}">{{ $pemesanan->status_pemesanan }}</span></td>
                    <td>
                        @php
                            $canActLevel1 = auth()->id() === $pemesanan->atasan_1_id && $pemesanan->status_pemesanan === 'menunggu_persetujuan';
                            $canActLevel2 = auth()->id() === $pemesanan->atasan_2_id && $pemesanan->status_pemesanan === 'disetujui_level_1';
                        @endphp

                        @if($canActLevel1 || $canActLevel2)
                            <form action="{{ route('persetujuan.update', $pemesanan) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="form-group mb-2">
                                    <input type="text" name="catatan_tambahan" class="form-control form-control-sm" placeholder="Catatan opsional">
                                </div>
                                <div class="btn-group">
                                    <button type="submit" name="aksi" value="setuju" class="btn btn-success btn-sm" data-confirm="Yakin ingin menyetujui pemesanan ini?" data-confirm-title="Konfirmasi Persetujuan" data-confirm-icon="question">Setuju</button>
                                    <button type="submit" name="aksi" value="tolak" class="btn btn-danger btn-sm" data-confirm="Yakin ingin menolak pemesanan ini?" data-confirm-title="Konfirmasi Penolakan" data-confirm-icon="warning">Tolak</button>
                                </div>
                            </form>
                        @else
                            Tidak ada aksi
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
