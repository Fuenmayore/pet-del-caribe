<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // REQUISITO FASE I
use Illuminate\Database\Eloquent\SoftDeletes;

class ParadaCatalogo extends Model 
{
    use HasUuids, SoftDeletes;

    protected $table = 'paradas_catalogo';

    // Asegúrate de que estos tres estén aquí:
    protected $fillable = ['codigo', 'falla', 'categoria']; 
}