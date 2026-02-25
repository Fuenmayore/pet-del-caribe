<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maquina extends Model
{
    use SoftDeletes; // Quita 'HasUuids' de aquí

    protected $fillable = ['nombre', 'sub_area'];

    public function turnos() {
        return $this->hasMany(Turno::class);
    }
}
