<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movimiento extends Model
{
    protected $table = 'movimientos';

    protected $fillable = [
        'movi_descripcion',
        'movi_fecha_ingreso',
        'movi_id_transaccion',
        'movi_monto_total',
        'movi_medio_pago',
        'movi_propina',
        'timo_id',
        'usua_id',
        'caja_id',
    ];

    protected $casts = [
        'movi_fecha_ingreso' => 'date',
    ];

    public function tipoMovimiento(): BelongsTo
    {
        return $this->belongsTo(TipoMovimiento::class, 'timo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usua_id');
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function productosDelMovimiento(): HasMany
    {
        return $this->hasMany(ProductosDelMovimiento::class, 'movi_id');
    }
}
