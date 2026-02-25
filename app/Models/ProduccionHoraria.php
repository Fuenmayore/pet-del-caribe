<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProduccionHoraria extends Model
{
    use HasUuids; // Según tu ERD, usas UUID
    protected $table = 'produccion_horaria';
    protected $fillable = ['config_id', 'hora', 'unidades_buenas'];

    // Relación: Una hora tiene muchos registros de desperdicio (PNC)
    public function registrosPnc() {
        return $this->hasMany(RegistrosPnc::class, 'produccion_hora_id');
    }   
}