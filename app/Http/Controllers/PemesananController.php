<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Kendaraan;
use App\Models\Pemesanan;
use App\Models\RiwayatPemakaian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PemesananController extends Controller
{
    public function index(): View
    {
        $selectedKendaraan = old('kendaraan_id')
            ? Kendaraan::with('kantor')->find(old('kendaraan_id'))
            : null;

        $selectedDriver = old('driver_id')
            ? Driver::find(old('driver_id'))
            : null;

        $selectedAtasan1 = old('atasan_1_id')
            ? User::where('role', 'penyetujui')->find(old('atasan_1_id'))
            : null;

        $selectedAtasan2 = old('atasan_2_id')
            ? User::where('role', 'penyetujui')->find(old('atasan_2_id'))
            : null;

        return view('pemesanan.index', [
            'pemesananList' => Pemesanan::query()
                ->with(['admin', 'kendaraan', 'driver', 'atasan1', 'atasan2', 'riwayatPemakaian'])
                ->latest()
                ->get(),
            'selectedKendaraan' => $selectedKendaraan,
            'selectedDriver' => $selectedDriver,
            'selectedAtasan1' => $selectedAtasan1,
            'selectedAtasan2' => $selectedAtasan2,
        ]);
    }

    public function searchKendaraan(Request $request): JsonResponse
    {
        $search = (string) $request->query('q', '');
        $page = max((int) $request->query('page', 1), 1);
        $perPage = 20;

        $query = Kendaraan::query()->with('kantor')->orderBy('nama');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('nama', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%")
                    ->orWhereHas('kantor', function ($kantorQuery) use ($search): void {
                        $kantorQuery->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'results' => $result->getCollection()->map(function (Kendaraan $kendaraan): array {
                return [
                    'id' => $kendaraan->id,
                    'text' => $kendaraan->nama.' - '.$kendaraan->jenis.' - '.($kendaraan->kantor->nama ?? '-'),
                ];
            })->values(),
            'pagination' => [
                'more' => $result->hasMorePages(),
            ],
        ]);
    }

    public function searchDriver(Request $request): JsonResponse
    {
        $search = (string) $request->query('q', '');
        $page = max((int) $request->query('page', 1), 1);
        $perPage = 20;

        $query = Driver::query()->orderBy('nama');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('nama', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%");
            });
        }

        $result = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'results' => $result->getCollection()->map(function (Driver $driver): array {
                return [
                    'id' => $driver->id,
                    'text' => $driver->nama.' ('.$driver->status.')',
                ];
            })->values(),
            'pagination' => [
                'more' => $result->hasMorePages(),
            ],
        ]);
    }

    public function searchPenyetuju(Request $request): JsonResponse
    {
        $search = (string) $request->query('q', '');
        $page = max((int) $request->query('page', 1), 1);
        $perPage = 20;

        $query = User::query()
            ->where('role', 'penyetujui')
            ->orderBy('nama');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $result = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'results' => $result->getCollection()->map(function (User $user): array {
                return [
                    'id' => $user->id,
                    'text' => $user->nama,
                ];
            })->values(),
            'pagination' => [
                'more' => $result->hasMorePages(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kendaraan_id' => ['required', Rule::exists('kendaraan', 'id')],
            'driver_id' => ['required', Rule::exists('driver', 'id')],
            'atasan_1_id' => ['required', Rule::exists('users', 'id')],
            'atasan_2_id' => ['required', Rule::exists('users', 'id'), 'different:atasan_1_id'],
            'tanggal_mulai' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'tanggal_selesai' => ['required', 'date'],
            'jam_selesai' => ['required', 'date_format:H:i'],
            'catatan' => ['nullable', 'string'],
        ]);

        $tanggalMulai = Carbon::createFromFormat('Y-m-d H:i', $data['tanggal_mulai'].' '.$data['jam_mulai']);
        $tanggalSelesai = Carbon::createFromFormat('Y-m-d H:i', $data['tanggal_selesai'].' '.$data['jam_selesai']);

        if ($tanggalSelesai->lessThanOrEqualTo($tanggalMulai)) {
            return back()->withErrors([
                'tanggal_selesai' => 'Tanggal & jam selesai harus setelah tanggal & jam mulai.',
            ])->withInput();
        }

        Pemesanan::create([
            'kendaraan_id' => $data['kendaraan_id'],
            'driver_id' => $data['driver_id'],
            'atasan_1_id' => $data['atasan_1_id'],
            'atasan_2_id' => $data['atasan_2_id'],
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'catatan' => $data['catatan'] ?? null,
            'admin_id' => Auth::id(),
            'status_pemesanan' => 'menunggu_persetujuan',
        ]);

        Driver::whereKey($data['driver_id'])->update(['status' => 'sibuk']);

        return back()->with('success', 'Pemesanan berhasil dibuat.');
    }

    public function storeRiwayat(Request $request, Pemesanan $pemesanan): RedirectResponse
    {
        if ($pemesanan->status_pemesanan !== 'disetujui_final') {
            return back()->with('error', 'Riwayat hanya bisa diisi untuk pemesanan final.');
        }

        $data = $request->validate([
            'jarak_tempuh_km' => ['required', 'numeric', 'min:0.1'],
            'bbm_terpakai_liter' => ['required', 'numeric', 'min:0.1'],
            'keterangan' => ['nullable', 'string'],
        ]);

        if ($pemesanan->riwayatPemakaian()->exists()) {
            return back()->with('error', 'Riwayat pemakaian untuk pemesanan ini sudah ada.');
        }

        RiwayatPemakaian::create([
            'pemesanan_id' => $pemesanan->id,
            ...$data,
        ]);

        Driver::whereKey($pemesanan->driver_id)->update(['status' => 'tersedia']);

        return back()->with('success', 'Riwayat pemakaian berhasil ditambahkan.');
    }
}
