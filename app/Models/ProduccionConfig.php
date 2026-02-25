<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes; // <--- Nuevo


class ProduccionConfig extends Model
{
    use HasUuids, SoftDeletes; // <--- Activa ambos
    protected $table = 'produccion_config';
    protected $fillable = ['turno_id', 'referencia', 'num_cavidades', 'materia_prima', 'porcentaje_molido'];

    public function horasProduccion() {
        return $this->hasMany(ProduccionHoraria::class, 'config_id');
    }

    public function producto()
{
    // Relacionamos el campo 'referencia' con el ID de la tabla productos
    return $this->belongsTo(Producto::class, 'referencia');
}
}