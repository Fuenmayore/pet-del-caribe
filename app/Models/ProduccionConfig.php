<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProduccionConfig extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'produccion_config';

    protected $fillable = [
        'turno_id', 
        'producto_id', // Sigue siendo el FK hacia la tabla productos (es la referencia que se va a producir)
        'mezcla_materiales'    // JSON: Array de materiales y porcentajes
    ];

    /**
     * Casts para manejar JSON como Arrays de PHP automáticamente
     */
    protected $casts = [
        'mezcla_materiales'   => 'array',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function horasProduccion(): HasMany
    {
        return $this->hasMany(ProduccionHoraria::class, 'config_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }
}