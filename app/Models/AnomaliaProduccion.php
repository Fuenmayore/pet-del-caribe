<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnomaliaProduccion extends Model
{
    use SoftDeletes; // Quita 'HasUuids' de aquí

    protected $table = 'anomalias_produccion';
    protected $fillable = ['codigo', 'descripcion'];
}