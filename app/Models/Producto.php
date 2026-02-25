<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla (opcional si sigue el estándar, pero mejor asegurar)
    protected $table = 'productos';

    /**
     * Campos habilitados para carga masiva (Mass Assignment).
     * IMPORTANTE: Estos deben coincidir con los que usas en el Seeder.
     */
    protected $fillable = [
        'item',
        'descripcion',
        'area',
        'ciclo',
        'cavidades',
        'materia_prima',
    ];

    /**
     * Casteo de tipos para asegurar que los números se manejen correctamente.
     */
    protected $casts = [
        'ciclo' => 'decimal:2',
        'cavidades' => 'integer',
    ];

    /**
     * Relación: Un producto puede estar en muchas configuraciones de producción.
     */
    public function configuraciones()
    {
        return $this->hasMany(ProduccionConfig::class, 'producto_id');
    }
}