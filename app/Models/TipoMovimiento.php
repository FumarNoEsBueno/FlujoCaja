<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoMovimiento extends Model
{
    protected $table = 'tipo_movimiento';

    protected $fillable = [
        'timo_nombre',
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'timo_id');
    }
}
