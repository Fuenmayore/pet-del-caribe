<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class MateriaPrima extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'materia_prima';

    protected $fillable = [
        'nombre',
        'tipo',
        'unidad_medida',
        'proveedor'
    ];
}