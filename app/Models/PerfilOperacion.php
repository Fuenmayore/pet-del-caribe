<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerfilOperacion extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'perfiles_operacion';

    protected $fillable = [
        'turno_id',
        'config_id',
        'hora_medicion',
        'clamp_expulsor',
        'inyeccion_carga',
        'temperaturas',
        'perifericos_presion',
        'observaciones',
        'registrado_por'
    ];

    protected $casts = [
        'clamp_expulsor' => 'array',
        'inyeccion_carga' => 'array',
        'temperaturas' => 'array',
        'perifericos_presion' => 'array',
    ];

    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

    public function configuracion()
    {
        return $this->belongsTo(ProduccionConfig::class, 'config_id');
    }

   public function registrador()
{
    // El segundo parámetro es la clave foránea real en tu tabla
    return $this->belongsTo(User::class, 'registrado_por');
}
}