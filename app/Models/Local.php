<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Local extends Model
{
    protected $table = 'locales';

    protected $fillable = [
        'loca_nombre',
        'loca_direccion',
        'comu_id',
    ];

    public function comuna(): BelongsTo
    {
        return $this->belongsTo(Comuna::class, 'comu_id');
    }

    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class, 'loca_id');
    }
}
