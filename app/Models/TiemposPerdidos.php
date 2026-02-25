<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class TiemposPerdidos extends Model
{
    use HasUuids, SoftDeletes; // <--- Activa ambos
    protected $table = 'tiempos_perdidos';
    protected $fillable = ['turno_id', 'item_motivo', 'hora_inicial', 'tiempo_perdido_min', 'observaciones'];
}