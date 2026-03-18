<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'role_nombre',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'role_id');
    }

    public function permisosPorRol(): HasMany
    {
        return $this->hasMany(PermisosPorRol::class, 'role_id');
    }

    /**
     * Permisos activos del rol (a través de la tabla pivot permisos_por_rol).
     */
    public function permisosActivos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'permisos_por_rol', 'role_id', 'perm_id')
            ->wherePivot('pero_activo', true);
    }
}
