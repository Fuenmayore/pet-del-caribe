<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes; // <--- Nuevo
use Illuminate\Database\Eloquent\Concerns\HasUuids; // <--- Nuevo

class Turno extends Model
{
    use HasUuids, SoftDeletes; // <--- Activa ambos

    protected $fillable = ['fecha', 'numero_turno', 'maquina_id', 'operario_id', 'supervisor', 'estado'];


    public function maquina() {
        return $this->belongsTo(Maquina::class);
    }

    public function produccionConfig() {
        return $this->hasOne(ProduccionConfig::class);
    }

    public function tiemposPerdidos() {
        return $this->hasMany(TiemposPerdidos::class);
    }
}