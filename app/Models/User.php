<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['nama', 'email', 'password', 'role', 'kantor_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_id');
    }

    public function pemesananSebagaiAdmin(): HasMany
    {
        return $this->hasMany(Pemesanan::class, 'admin_id');
    }

    public function persetujuanLevel1(): HasMany
    {
        return $this->hasMany(Pemesanan::class, 'atasan_1_id');
    }

    public function persetujuanLevel2(): HasMany
    {
        return $this->hasMany(Pemesanan::class, 'atasan_2_id');
    }

    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class, 'user_id');
    }
}
