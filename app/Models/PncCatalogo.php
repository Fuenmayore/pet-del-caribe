<?php
// app/Models/PncCatalogo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // [cite: 23]
use Illuminate\Database\Eloquent\SoftDeletes; // 

class PncCatalogo extends Model {
    use HasUuids, SoftDeletes;
    protected $table = 'pnc_catalogo';
    protected $fillable = ['nombre', 'area'];
}