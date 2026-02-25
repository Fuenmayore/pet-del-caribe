<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Importante para UUID
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles; // Importante para Roles


class User extends Authenticatable
{
    use HasUuids, HasRoles, Notifiable, SoftDeletes;

    // ESTAS 2 LÍNEAS SON VITALES PARA UUID
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nombre',
        'codigo_empleado',
        'email',
        'password',
        'activo',
        'email_verified_at', // Asegúrate de que esté aquí
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
