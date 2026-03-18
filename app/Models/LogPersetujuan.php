<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogPersetujuan extends Model
{
    use HasFactory;

    protected $table = 'log_persetujuan';

    protected $fillable = [
        'pemesanan_id',
        'penyetujui_id',
        'level',
        'aksi',
        'catatan_tambahan',
    ];

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }

    public function penyetujui(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penyetujui_id');
    }
}
