<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = [
        'caja_nombre',
        'loca_id',
    ];

    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'loca_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'caja_id');
    }

    public function usuariosPorCaja(): HasMany
    {
        return $this->hasMany(UsuariosPorCaja::class, 'caja_id');
    }
}
