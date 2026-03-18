<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPemakaian extends Model
{
    use HasFactory;

    protected $table = 'riwayat_pemakaian';

    protected $fillable = [
        'pemesanan_id',
        'jarak_tempuh_km',
        'bbm_terpakai_liter',
        'keterangan',
    ];

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }
}
