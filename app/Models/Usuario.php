<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    protected $table = 'usuarios';

    protected $fillable = [
        'usua_nombre',
        'usua_apellido_p',
        'usua_apellido_m',
        'usua_rut',
        'usua_dv',
        'usua_correo',
        'usua_fecha_nac',
        'usua_password',
        'role_id',
    ];

    protected $hidden = [
        'usua_password',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'usua_fecha_nac' => 'date',
    ];

    // ─── JWTSubject ──────────────────────────────────────────────────────────

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /** @return array<string, mixed> */
    public function getJWTCustomClaims(): array
    {
        return [
            'role_id' => $this->role_id,
        ];
    }

    // ─── Auth: campo de password ──────────────────────────────────────────────

    /**
     * Authenticatable espera getAuthPassword().
     * Nuestro campo se llama usua_password, así que lo mapeamos.
     */
    public function getAuthPassword(): string
    {
        return $this->usua_password;
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'role_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'usua_id');
    }

    public function usuariosPorCaja(): HasMany
    {
        return $this->hasMany(UsuariosPorCaja::class, 'usua_id');
    }
}
