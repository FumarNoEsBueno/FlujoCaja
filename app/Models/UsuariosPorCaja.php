<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuariosPorCaja extends Model
{
    protected $table = 'usuarios_por_caja';

    protected $fillable = [
        'usua_id',
        'caja_id',
        'usca_habilitado',
        'usca_fecha_inicio',
    ];

    protected $casts = [
        'usca_habilitado' => 'boolean',
        'usca_fecha_inicio' => 'date',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usua_id');
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }
}
