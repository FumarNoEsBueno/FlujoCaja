<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comuna extends Model
{
    protected $table = 'comuna';

    protected $fillable = [
        'comu_nombre',
        'regi_id',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'regi_id');
    }

    public function locales(): HasMany
    {
        return $this->hasMany(Local::class, 'comu_id');
    }
}
