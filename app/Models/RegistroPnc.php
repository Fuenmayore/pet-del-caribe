<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RegistrosPnc extends Model
{
    use HasUuids;
    protected $table = 'registros_pnc';
    protected $fillable = [
        'produccion_hora_id', 'anomalia_id', 'unid_malas', 
        'unid_contaminadas', 'unid_torta', 'causa'
    ];

    // Relación: Este desperdicio pertenece a una hora específica
    public function produccionHoraria() {
        return $this->belongsTo(ProduccionHoraria::class);
    }
}