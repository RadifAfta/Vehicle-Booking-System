<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kendaraan extends Model
{
    use HasFactory;

    protected $table = 'kendaraan';

    protected $fillable = [
        'nama',
        'jenis',
        'kepemilikan',
        'kantor_id',
        'konsumsi_bbm_liter_per_km',
        'tanggal_servis_terakhir',
    ];

    protected $casts = [
        'tanggal_servis_terakhir' => 'date',
    ];

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_id');
    }

    public function pemesanan(): HasMany
    {
        return $this->hasMany(Pemesanan::class, 'kendaraan_id');
    }
}
