<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProduccionHoraria extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'produccion_horaria';

    protected $fillable = [
        'config_id', 
        'hora', 
        'num_vale_inyectora', 
        'unidades_buenas', 
        'pnc_detalle' // JSON: Aquí vive todo el desperdicio de la hora
    ];

    /**
     * Cast para que al llamar $hora->pnc_detalle recibas un array
     */
    protected $casts = [
        'pnc_detalle' => 'array',
    ];

    public function configuracion(): BelongsTo
    {
        return $this->belongsTo(ProduccionConfig::class, 'config_id');
    }
}