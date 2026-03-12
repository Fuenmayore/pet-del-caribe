<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    /**
     * Campos habilitados para carga masiva.
     * Sincronizados con la tabla de Excel (REF + MAQ, Item, Desc, etc.)
     */
    protected $fillable = [
        'ref_maq',          // REF + MAQ
        'item',             // Item
        'descripcion',      // Desc. item
        'tipo_inventario',  // Tipo inventari
        'area',             // AREA
        'preforma',         // Preforma
        'ciclo',            // Ciclo
        'maquina',          // Maquina
        'cavidades',        // Cavidades
        'bot_hora',         // BOT/HORA
        'unidad_empaque',   // UNIDAD DE EMPAQUE
        'centro_trabajo'    // Centro de trab
    ];

    /**
     * Casteo de tipos. 
     * Vital para que el Dashboard de Análisis reciba números reales y no strings.
     */
    protected $casts = [
        'ciclo'     => 'decimal:2',
        'cavidades' => 'integer',
        'bot_hora'  => 'integer',
    ];

    /**
     * Relación: Un producto puede estar en muchas configuraciones de producción.
     */
    public function configuraciones()
    {
        return $this->hasMany(ProduccionConfig::class, 'producto_id');
    }
}