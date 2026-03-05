<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Turno extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['fecha', 'numero_turno', 'maquina_id','duracion_turno', 'operario_id', 'supervisor', 'estado', 'area'];


    // --- NUEVA RELACIÓN ---
    public function operario() {
        // Conectamos el operario_id con la tabla de usuarios
        return $this->belongsTo(User::class, 'operario_id');
    }
    
    public function maquina() {
        return $this->belongsTo(Maquina::class);
    }

    // Un turno tiene muchas fases de configuración (Trazabilidad)
    public function configuraciones() {
        return $this->hasMany(ProduccionConfig::class, 'turno_id');
    }

    // ESTA ES LA CLAVE: Obtiene la configuración que el operario acaba de guardar
    public function configActiva() {
        return $this->hasOne(ProduccionConfig::class, 'turno_id')->latestOfMany();
    }

    public function tiemposPerdidos() {
        return $this->hasMany(TiemposPerdidos::class);
    }
}