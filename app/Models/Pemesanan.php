<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';

    protected $fillable = [
        'admin_id',
        'kendaraan_id',
        'driver_id',
        'atasan_1_id',
        'atasan_2_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_pemesanan',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function atasan1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_1_id');
    }

    public function atasan2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_2_id');
    }

    public function logPersetujuan(): HasMany
    {
        return $this->hasMany(LogPersetujuan::class, 'pemesanan_id');
    }

    public function riwayatPemakaian(): HasMany
    {
        return $this->hasMany(RiwayatPemakaian::class, 'pemesanan_id');
    }
}
