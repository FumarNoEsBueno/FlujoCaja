<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Model
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

    protected $casts = [
        'usua_fecha_nac' => 'date',
    ];

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
