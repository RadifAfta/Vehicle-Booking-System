@extends('layouts.app')

@section('content')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Input Pemesanan Kendaraan</h1>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Form Pemesanan</h3>
        </div>
        <div class="card-body">
        <form action="{{ route('pemesanan.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Kendaraan</label>
                    <select name="kendaraan_id" class="form-control js-search-select" data-ajax-url="{{ route('pemesanan.search.kendaraan') }}" data-placeholder="Ketik nama kendaraan / jenis / kantor" required>
                        <option value="">Pilih Kendaraan</option>
                        @if($selectedKendaraan)
                            <option value="{{ $selectedKendaraan->id }}" selected>{{ $selectedKendaraan->nama }} - {{ $selectedKendaraan->jenis }} - {{ $selectedKendaraan->kantor->nama ?? '-' }}</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Driver</label>
                    <select name="driver_id" class="form-control js-search-select" data-ajax-url="{{ route('pemesanan.search.driver') }}" data-placeholder="Ketik nama driver" required>
                        <option value="">Pilih Driver</option>
                        @if($selectedDriver)
                            <option value="{{ $selectedDriver->id }}" selected>{{ $selectedDriver->nama }} ({{ $selectedDriver->status }})</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Penyetuju Level 1</label>
                    <select name="atasan_1_id" class="form-control js-search-select" data-ajax-url="{{ route('pemesanan.search.penyetuju') }}" data-placeholder="Ketik nama penyetuju" required>
                        <option value="">Pilih Penyetuju 1</option>
                        @if($selectedAtasan1)
                            <option value="{{ $selectedAtasan1->id }}" selected>{{ $selectedAtasan1->nama }}</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Penyetuju Level 2</label>
                    <select name="atasan_2_id" class="form-control js-search-select" data-ajax-url="{{ route('pemesanan.search.penyetuju') }}" data-placeholder="Ketik nama penyetuju" required>
                        <option value="">Pilih Penyetuju 2</option>
                        @if($selectedAtasan2)
                            <option value="{{ $selectedAtasan2->id }}" selected>{{ $selectedAtasan2->nama }}</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="form-control" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Jam Mulai</label>
                    <input type="text" name="jam_mulai" value="{{ old('jam_mulai') }}" class="form-control js-time-picker" placeholder="Pilih jam mulai" readonly required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="form-control" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Jam Selesai</label>
                    <input type="text" name="jam_selesai" value="{{ old('jam_selesai') }}" class="form-control js-time-picker" placeholder="Pilih jam selesai" readonly required>
                </div>
                <div class="col-md-12 form-group">
                    <label>Catatan</label>
                    <textarea name="catatan" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-primary" data-confirm="Yakin simpan pemesanan ini?" data-confirm-title="Simpan Pemesanan" data-confirm-icon="question">Simpan Pemesanan</button>
            </div>
        </form>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Daftar Pemesanan</h3>
        </div>
        <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap mb-0">
            <thead>
            <tr>
                <th>ID</th>
                <th>Kendaraan</th>
                <th>Driver</th>
                <th>Periode</th>
                <th>Status</th>
                <th>Riwayat Pemakaian</th>
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
                    <td>{{ $pemesanan->kendaraan->nama }}</td>
                    <td>{{ $pemesanan->driver->nama }}</td>
                    <td>{{ $pemesanan->tanggal_mulai }} s/d {{ $pemesanan->tanggal_selesai }}</td>
                    <td>
                        <span class="status-pill {{ $statusClass }}">{{ $pemesanan->status_pemesanan }}</span>
                    </td>
                    <td>
                        @if($pemesanan->riwayatPemakaian->isNotEmpty())
                            <span class="badge badge-success">Sudah diisi</span>
                        @elseif($pemesanan->status_pemesanan === 'disetujui_final')
                            <form action="{{ route('pemesanan.riwayat.store', $pemesanan) }}" method="POST">
                                @csrf
                                <div class="form-group mb-2">
                                    <input type="number" step="0.1" min="0" name="jarak_tempuh_km" placeholder="KM" class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-2">
                                    <input type="number" step="0.1" min="0" name="bbm_terpakai_liter" placeholder="BBM (L)" class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-2">
                                    <input type="text" name="keterangan" placeholder="Keterangan" class="form-control form-control-sm">
                                </div>
                                <button type="submit" class="btn btn-success btn-sm" data-confirm="Yakin simpan riwayat pemakaian ini?" data-confirm-title="Simpan Riwayat" data-confirm-icon="question">Simpan</button>
                            </form>
                        @else
                            <span class="badge badge-secondary">Menunggu approval final</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada pemesanan.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
