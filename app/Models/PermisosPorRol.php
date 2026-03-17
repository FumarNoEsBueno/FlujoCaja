<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermisosPorRol extends Model
{
    protected $table = 'permisos_por_rol';

    protected $fillable = [
        'perm_id',
        'role_id',
        'pero_activo',
    ];

    protected $casts = [
        'pero_activo' => 'boolean',
    ];

    public function permiso(): BelongsTo
    {
        return $this->belongsTo(Permiso::class, 'perm_id');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'role_id');
    }
}
