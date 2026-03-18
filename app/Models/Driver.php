<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $table = 'driver';

    protected $fillable = [
        'nama',
        'telepon',
        'status',
    ];

    public function pemesanan(): HasMany
    {
        return $this->hasMany(Pemesanan::class, 'driver_id');
    }
}
