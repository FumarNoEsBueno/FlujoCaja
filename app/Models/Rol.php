<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
}
