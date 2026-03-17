<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'producto';

    protected $fillable = [
        'prod_nombre',
        'prod_precio',
    ];

    public function productosDelMovimiento(): HasMany
    {
        return $this->hasMany(ProductosDelMovimiento::class, 'prod_id');
    }
}
