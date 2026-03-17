<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductosDelMovimiento extends Model
{
    protected $table = 'productos_del_movimiento';

    protected $fillable = [
        'pdmo_cantidad',
        'pdmo_monto_unitario',
        'movi_id',
        'prod_id',
    ];

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class, 'movi_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'prod_id');
    }
}
