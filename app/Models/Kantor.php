<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kantor extends Model
{
    use HasFactory;

    protected $table = 'kantor';

    protected $fillable = [
        'nama',
        'tipe',
        'wilayah',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'kantor_id');
    }

    public function kendaraan(): HasMany
    {
        return $this->hasMany(Kendaraan::class, 'kantor_id');
    }
}
